<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\VagaAbertaController;
use App\Models\Cliente;
use App\Models\VagasAbertas;
use Illuminate\Support\Facades\Cache;
use App\Contracts\IntegracaoSpa\EmpresaIntegracaoSpaQuery;
use Illuminate\Http\Request;

/**
 * BFF público do portal de vagas: amarra empresa pela rota e delega aos sinks
 * de currículo sem expor X-API-TOKEN ao browser.
 */
class PortalVagasBffController extends Controller
{
    /** Apelidos internos que não entram na vitrine pública do portal. */
    private const APELIDOS_EXCLUIDOS_VITRINE = ['_bpservice', 'mybp'];

    public function atualizarVaga(Request $request, int $empresa_id, int $vaga_aberta_id)
    {
        $this->garantirEmpresaAtiva($empresa_id);
        $request->merge([
            'empresa_id' => $empresa_id,
            'vaga_aberta_id' => $vaga_aberta_id,
        ]);

        return app(VagaAbertaController::class)->atualizar($request);
    }

    public function buscaCurriculo(Request $request, int $empresa_id)
    {
        $this->garantirEmpresaAtiva($empresa_id);
        $request->merge(['empresa_id' => $empresa_id]);

        return app(VagaAbertaController::class)->buscaCurriculo($request);
    }

    public function buscaCpf(Request $request, int $empresa_id)
    {
        $this->garantirEmpresaAtiva($empresa_id);
        $request->merge(['empresa_id' => $empresa_id]);

        return app(VagaAbertaController::class)->buscaCpf($request);
    }

    public function cadastraCurriculo(Request $request, int $empresa_id)
    {
        $this->garantirEmpresaAtiva($empresa_id);
        $request->merge(['empresa_id' => $empresa_id]);

        return app(VagaAbertaController::class)->store($request);
    }

    /**
     * Lista empresas ativas com portal de vagas (vitrine).
     * Whitelist: sem id interno, CNPJ, endereço completo ou textos institucionais.
     * Respeita allowlist X_API_TOKEN_EMPRESA_IDS do middleware apitoken.
     */
    public function empresasAtivas(Request $request, EmpresaIntegracaoSpaQuery $empresas)
    {
        $contagem = Cache::remember('portal_vagas:contagem_por_empresa', now()->addMinutes(5), static function (): array {
            return VagasAbertas::withoutGlobalScopes()
                ->where('ativo', true)
                ->selectRaw('empresa_id, COUNT(*) as total')
                ->groupBy('empresa_id')
                ->pluck('total', 'empresa_id')
                ->map(static fn ($t) => (int) $t)
                ->all();
        });

        $lista = $empresas->listarEmpresasAtivas();
        $credencial = $request->attributes->get('api_integracao');
        $allow = is_array($credencial) ? ($credencial['empresa_ids'] ?? []) : [];
        if ($allow !== []) {
            $allowFlip = array_flip(array_map('intval', $allow));
            $lista = array_values(array_filter(
                $lista,
                static fn ($dto) => isset($allowFlip[(int) $dto->id])
            ));
        }

        $excluidos = array_flip(self::APELIDOS_EXCLUIDOS_VITRINE);
        $lista = array_values(array_filter(
            $lista,
            static function ($dto) use ($excluidos): bool {
                $apelido = mb_strtolower(trim((string) ($dto->apelido ?? '')));

                return $apelido !== '' && ! isset($excluidos[$apelido]);
            }
        ));

        $dados = array_map(static fn ($dto) => [
            'apelido' => $dto->apelido,
            'nome' => $dto->razao_social,
            'municipio' => $dto->endereco['municipio'] ?? null,
            'uf' => $dto->endereco['uf'] ?? null,
            'logo_url' => $dto->logotipo?->url_thumb ?? $dto->logotipo?->url,
            'total_vagas' => $contagem[$dto->id] ?? 0,
        ], $lista);

        // Vagas recentes (só cargo e local, sem identificar a empresa) para a vitrine.
        $ids = array_map(static fn ($dto) => (int) $dto->id, $lista);
        $destaques = Cache::remember(
            'portal_vagas:recentes:' . md5(implode(',', $ids)),
            now()->addMinutes(5),
            static function () use ($ids): array {
                if ($ids === []) {
                    return [];
                }

                return VagasAbertas::withoutGlobalScopes()
                    ->select(['id', 'titulo', 'municipio_id'])
                    ->where('ativo', true)
                    ->whereIn('empresa_id', $ids)
                    ->whereNotNull('titulo')
                    ->where('titulo', '!=', '')
                    ->with('Municipio:id,nome,uf')
                    ->orderByDesc('updated_at')
                    ->limit(40)
                    ->get()
                    ->unique(static fn ($v) => mb_strtolower(trim((string) $v->titulo)))
                    ->take(6)
                    ->map(static fn ($v) => [
                        'titulo' => mb_substr(trim((string) $v->titulo), 0, 80),
                        'municipio' => $v->Municipio?->nome,
                        'uf' => $v->Municipio?->uf,
                    ])
                    ->values()
                    ->all();
            }
        );

        return response()
            ->json(['success' => true, 'dados' => array_values($dados), 'destaques' => $destaques])
            ->header('Cache-Control', 'private, max-age=300');
    }

    private function garantirEmpresaAtiva(int $empresaId): void
    {
        $existe = Cliente::withoutGlobalScopes()
            ->where('id', $empresaId)
            ->where('ativo', true)
            ->exists();

        abort_unless($existe, 404, 'Empresa não encontrada');
    }
}
