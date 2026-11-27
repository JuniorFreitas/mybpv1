<?php

namespace App\Services\Notificacao;

use App\Helpers\RHHelper;
use App\Models\CentroCustoFilial;
use App\Models\NotificacaoGrupo;
use App\Models\NotificacaoTipo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class NotificacaoDestinatarioResolver
{
    private const CACHE_TTL = 600;

    /**
     * @param  array{centro_custo_id?: int|null, matriz?: bool|null}  $contexto
     * @return list<string>
     */
    public function emails(int $empresaId, string $tipoCodigo, array $contexto = []): array
    {
        $resolver = fn () => $this->resolverEmails($empresaId, $tipoCodigo, $contexto);
        $cacheKey = $this->cacheKey($empresaId, $tipoCodigo, $contexto);

        if (!$this->cacheSuportaTags()) {
            return $resolver();
        }

        return Cache::tags($this->cacheTags($empresaId))->remember(
            $cacheKey,
            self::CACHE_TTL,
            $resolver
        );
    }

    public function invalidarEmpresa(int $empresaId): void
    {
        if (!$this->cacheSuportaTags()) {
            return;
        }

        Cache::tags($this->cacheTags($empresaId))->flush();
    }

    private function cacheSuportaTags(): bool
    {
        return method_exists(Cache::getStore(), 'tags');
    }

    /**
     * @param  array{centro_custo_id?: int|null, matriz?: bool|null}  $contexto
     * @return list<string>
     */
    private function resolverEmails(int $empresaId, string $tipoCodigo, array $contexto): array
    {
        $grupos = NotificacaoGrupo::query()
            ->withoutGlobalScopes()
            ->select(['id', 'empresa_id', 'tipo_codigo', 'ativo', 'escopo_cc', 'deleted_at'])
            ->where('empresa_id', $empresaId)
            ->where('tipo_codigo', $tipoCodigo)
            ->where('ativo', true)
            ->whereNull('deleted_at')
            ->with([
                'destinatarios' => function ($q) use ($empresaId) {
                    $q->withoutGlobalScopes()
                        ->select(['users.id', 'users.login', 'users.ativo', 'users.empresa_id', 'users.deleted_at'])
                        ->where('users.empresa_id', $empresaId)
                        ->where('users.ativo', true)
                        ->whereNull('users.deleted_at')
                        ->whereNotNull('users.login')
                        ->where('users.login', '!=', '');
                },
                'centrosCusto:id',
            ])
            ->get();

        if ($grupos->isEmpty()) {
            Log::info('NotificacaoDestinatarioResolver: nenhum grupo ativo', [
                'empresa_id' => $empresaId,
                'tipo' => $tipoCodigo,
                'destinatarios' => 0,
            ]);

            return [];
        }

        $permitidosRh = $this->mapaUsuariosRhSeAplicavel($empresaId, $tipoCodigo);

        $centroCustoId = isset($contexto['centro_custo_id']) ? (int) $contexto['centro_custo_id'] : null;
        $matriz = array_key_exists('matriz', $contexto) && $contexto['matriz'] !== null
            ? (bool) $contexto['matriz']
            : ($centroCustoId ? $this->centroCustoEhMatriz($centroCustoId, $empresaId) : null);

        $seen = [];
        $emails = [];

        foreach ($grupos as $grupo) {
            if (!$this->grupoCasaEscopo($grupo, $centroCustoId, $matriz)) {
                continue;
            }

            foreach ($grupo->destinatarios as $user) {
                if ($permitidosRh !== null && !isset($permitidosRh[(int) $user->id])) {
                    continue;
                }
                $login = strtolower(trim((string) $user->login));
                if ($login === '' || isset($seen[$login])) {
                    continue;
                }
                $seen[$login] = true;
                $emails[] = (string) $user->login;
            }
        }

        Log::info('NotificacaoDestinatarioResolver: resolvido', [
            'empresa_id' => $empresaId,
            'tipo' => $tipoCodigo,
            'destinatarios' => count($emails),
            'tem_contexto_cc' => $centroCustoId !== null,
        ]);

        return $emails;
    }

    /**
     * Para tipos com regra privilegios_rh, só envia a quem ainda tem o privilégio.
     *
     * @return array<int, true>|null null = sem filtro extra
     */
    private function mapaUsuariosRhSeAplicavel(int $empresaId, string $tipoCodigo): ?array
    {
        $tipo = NotificacaoTipo::query()->where('codigo', $tipoCodigo)->first();
        if (!$tipo || $tipo->regra_elegibilidade !== NotificacaoTipo::REGRA_PRIVILEGIOS_RH) {
            return null;
        }

        $mapa = [];
        foreach (RHHelper::idsUsuariosRhEmpresa($empresaId) as $id) {
            $mapa[(int) $id] = true;
        }

        return $mapa;
    }

    private function grupoCasaEscopo(NotificacaoGrupo $grupo, ?int $centroCustoId, ?bool $matriz): bool
    {
        $escopo = (string) $grupo->escopo_cc;

        if ($centroCustoId === null) {
            return $escopo === NotificacaoTipo::ESCOPO_TODOS;
        }

        if ($escopo === NotificacaoTipo::ESCOPO_TODOS) {
            return true;
        }

        if ($escopo === NotificacaoTipo::ESCOPO_MATRIZ) {
            return $matriz === true;
        }

        if ($escopo === NotificacaoTipo::ESCOPO_FILIAIS) {
            return $matriz === false;
        }

        if ($escopo === NotificacaoTipo::ESCOPO_ESPECIFICOS) {
            $ids = $grupo->centrosCusto->pluck('id')->map(fn ($id) => (int) $id)->all();

            return in_array($centroCustoId, $ids, true);
        }

        return false;
    }

    private function centroCustoEhMatriz(int $centroCustoId, int $empresaId): bool
    {
        $temFilial = CentroCustoFilial::query()
            ->withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('centro_custo_id', $centroCustoId)
            ->whereNull('deleted_at')
            ->exists();

        return !$temFilial;
    }

    /**
     * @param  array{centro_custo_id?: int|null, matriz?: bool|null}  $contexto
     */
    private function cacheKey(int $empresaId, string $tipoCodigo, array $contexto): string
    {
        $cc = $contexto['centro_custo_id'] ?? 'none';
        $matriz = array_key_exists('matriz', $contexto)
            ? ($contexto['matriz'] === null ? 'null' : ($contexto['matriz'] ? '1' : '0'))
            : 'auto';

        return "notificacao_destinatarios:{$empresaId}:{$tipoCodigo}:cc{$cc}:m{$matriz}";
    }

    /**
     * @return list<string>
     */
    private function cacheTags(int $empresaId): array
    {
        return ['notificacao_destinatarios', "empresa_{$empresaId}"];
    }
}
