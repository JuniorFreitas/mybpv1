<?php

namespace App\Services\Whatsapp;

use App\Domain\Whatsapp\Enums\TipoMensagemWhatsapp;
use App\Domain\Whatsapp\Services\WhatsappConfigService;
use App\Domain\Whatsapp\Services\WhatsappNotificationGateService;
use App\Domain\Whatsapp\Services\WhatsappUsuarioTelefoneResolver;
use App\Models\User;
use App\Models\UsuarioWhatsappPreferencia;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class WhatsappUsuarioNotificacaoService
{
    public function __construct(
        private readonly WhatsappConfigService $configService,
        private readonly WhatsappNotificationGateService $gate,
        private readonly WhatsappUsuarioTelefoneResolver $telefoneResolver,
    ) {
    }

    public function listarUsuarios(int $empresaId, array $filtros = []): LengthAwarePaginator
    {
        $porPagina = min(max((int) ($filtros['por_pagina'] ?? 25), 10), 100);
        $busca = trim((string) ($filtros['busca'] ?? ''));
        $filtroApto = (string) ($filtros['apto_whatsapp'] ?? '');
        $filtroRecebeMovimentacao = (string) ($filtros['recebe_movimentacao'] ?? '');

        $query = User::withoutGlobalScopes()
            ->select(['id', 'nome', 'login', 'ativo', 'empresa_id'])
            ->where('empresa_id', $empresaId)
            ->where('ativo', true);

        if ($busca !== '') {
            $query->where(function ($builder) use ($busca) {
                $builder->where('nome', 'like', "%{$busca}%")
                    ->orWhere('login', 'like', "%{$busca}%");
            });
        }

        if ($filtroApto === 'sim') {
            $query->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('curriculo_telefone')
                    ->whereColumn('curriculo_telefone.curriculo_id', 'users.id')
                    ->where('curriculo_telefone.tipo', 'whatsapp')
                    ->whereNotNull('curriculo_telefone.numero')
                    ->where('curriculo_telefone.numero', '!=', '');
            });
        } elseif ($filtroApto === 'nao') {
            $query->whereNotExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('curriculo_telefone')
                    ->whereColumn('curriculo_telefone.curriculo_id', 'users.id')
                    ->where('curriculo_telefone.tipo', 'whatsapp')
                    ->whereNotNull('curriculo_telefone.numero')
                    ->where('curriculo_telefone.numero', '!=', '');
            });
        }

        if ($filtroRecebeMovimentacao === 'nao') {
            $query->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('usuario_whatsapp_preferencias')
                    ->whereColumn('usuario_whatsapp_preferencias.user_id', 'users.id')
                    ->where('usuario_whatsapp_preferencias.modulo', 'Movimentação')
                    ->where('usuario_whatsapp_preferencias.receber', false);
            });
        } elseif ($filtroRecebeMovimentacao === 'sim') {
            $query->whereNotExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('usuario_whatsapp_preferencias')
                    ->whereColumn('usuario_whatsapp_preferencias.user_id', 'users.id')
                    ->where('usuario_whatsapp_preferencias.modulo', 'Movimentação')
                    ->where('usuario_whatsapp_preferencias.receber', false);
            });
        }

        $paginator = $query->orderBy('nome')->paginate($porPagina);
        $userIds = $paginator->getCollection()->pluck('id')->all();

        $preferenciasPorUsuario = $this->carregarPreferenciasPorUsuarios($userIds);
        $modulosEmpresa = $this->configService->listModulosHabilitadosForApi($empresaId);
        $whatsappLiberado = $this->gate->empresaPermiteWhatsapp($empresaId);

        $itens = $paginator->getCollection()->map(function (User $usuario) use (
            $empresaId,
            $preferenciasPorUsuario,
            $modulosEmpresa,
            $whatsappLiberado,
        ) {
            return $this->montarItemUsuario(
                $usuario,
                $empresaId,
                $preferenciasPorUsuario->get($usuario->id, collect())->keyBy('modulo'),
                $modulosEmpresa,
                $whatsappLiberado,
            );
        });

        $paginator->setCollection($itens);

        return $paginator;
    }

    public function atualizarPreferenciaUsuario(
        int $empresaId,
        int $userId,
        string $modulo,
        bool $receber,
    ): array {
        $usuario = User::withoutGlobalScopes()
            ->select(['id', 'nome', 'login', 'empresa_id', 'ativo'])
            ->where('id', $userId)
            ->where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->firstOrFail();

        if (!in_array($modulo, TipoMensagemWhatsapp::modulosLista(), true)) {
            abort(422, 'Módulo inválido.');
        }

        $this->configService->savePreferenciasUsuario($usuario->id, [[
            'modulo' => $modulo,
            'receber' => $receber,
        ]]);

        $preferenciasUsuario = $this->carregarPreferenciasPorUsuarios([$usuario->id])
            ->get($usuario->id, collect())
            ->keyBy('modulo');
        $modulosEmpresa = $this->configService->listModulosHabilitadosForApi($empresaId);

        return $this->montarItemUsuario(
            $usuario,
            $empresaId,
            $preferenciasUsuario,
            $modulosEmpresa,
            $this->gate->empresaPermiteWhatsapp($empresaId),
        );
    }

    /** @return Collection<int, Collection<int, UsuarioWhatsappPreferencia>> */
    private function carregarPreferenciasPorUsuarios(array $userIds): Collection
    {
        if ($userIds === []) {
            return collect();
        }

        return UsuarioWhatsappPreferencia::query()
            ->select(['user_id', 'modulo', 'receber'])
            ->whereIn('user_id', $userIds)
            ->get()
            ->groupBy('user_id');
    }

  /** @param Collection<string, UsuarioWhatsappPreferencia> $preferenciasUsuario */
    private function montarItemUsuario(
        User $usuario,
        int $empresaId,
        Collection $preferenciasUsuario,
        array $modulosEmpresa,
        bool $whatsappLiberado,
    ): array {
        $telefone = $this->telefoneResolver->resolverStatus($usuario->id);
        $preferencias = [];

        foreach ($modulosEmpresa as $moduloEmpresa) {
            $modulo = $moduloEmpresa['modulo'];
            $registro = $preferenciasUsuario->get($modulo);
            $receber = $registro !== null ? (bool) $registro->receber : true;
            $habilitadoEmpresa = (bool) $moduloEmpresa['habilitado'];
            $aptoEnvio = $whatsappLiberado
                && $habilitadoEmpresa
                && $telefone['tem_whatsapp']
                && $receber;

            $preferencias[$modulo] = [
                'modulo' => $modulo,
                'receber' => $receber,
                'habilitado_empresa' => $habilitadoEmpresa,
                'apto_envio' => $aptoEnvio,
                'tipos' => $moduloEmpresa['tipos'],
            ];
        }

        return [
            'id' => $usuario->id,
            'nome' => $usuario->nome,
            'login' => $usuario->login,
            'telefone' => $telefone,
            'whatsapp_liberado_empresa' => $whatsappLiberado,
            'pode_receber_whatsapp' => $whatsappLiberado && $telefone['tem_whatsapp'],
            'preferencias' => $preferencias,
        ];
    }
}
