<?php

namespace App\Domain\SaudeOcupacional\Pgr;

use App\Models\PgrInventario;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Leitura do PGR: versão vigente e riscos por cargo (vaga_id → ids de exame_riscos).
 * Cache versionado por empresa, invalidado ao publicar/alterar o PGR.
 */
class PgrVigenteRepositorio
{
    private const TTL_SEGUNDOS = 600;

    private const TTL_VERSAO_SEGUNDOS = 86400;

    /**
     * @return array{id: int, versao: int, vigencia_inicio: string|null}|null
     */
    public function vigente(int $empresaId): ?array
    {
        return $this->cache($empresaId)['vigente'];
    }

    /**
     * @return array<int, list<int>>
     */
    public function riscosPorCargo(int $empresaId): array
    {
        return $this->cache($empresaId)['riscos'];
    }

    /**
     * Riscos por cargo de uma versão qualquer (ex.: rascunho, para a prévia). Sem cache.
     *
     * @return array<int, list<int>>
     */
    public function riscosPorCargoDoInventario(int $inventarioId): array
    {
        $mapa = [];
        DB::table('pgr_inventarios_cargos as c')
            ->join('pgr_inventarios_riscos as r', 'r.pgr_inventario_cargo_id', '=', 'c.id')
            ->where('c.pgr_inventario_id', $inventarioId)
            ->orderBy('c.vaga_id')
            ->get(['c.vaga_id', 'r.exame_risco_id'])
            ->each(function ($l) use (&$mapa) {
                $mapa[(int) $l->vaga_id][] = (int) $l->exame_risco_id;
            });

        return $mapa;
    }

    public function invalidar(int $empresaId): void
    {
        Cache::put($this->chaveVersao($empresaId), $this->versao($empresaId) + 1, self::TTL_VERSAO_SEGUNDOS);
    }

    /**
     * @return array{vigente: array<string, mixed>|null, riscos: array<int, list<int>>}
     */
    private function cache(int $empresaId): array
    {
        $chave = sprintf('saude_ocupacional:pgr:%d:v%d', $empresaId, $this->versao($empresaId));

        return Cache::remember($chave, self::TTL_SEGUNDOS, function () use ($empresaId) {
            // Migration ainda não rodada (ou desfeita): sem PGR.
            if (!Schema::hasTable(PgrInventario::TABELA)) {
                return ['vigente' => null, 'riscos' => []];
            }

            $vigente = DB::table('pgr_inventarios')
                ->where('empresa_id', $empresaId)
                ->where('status', PgrInventario::STATUS_VIGENTE)
                ->whereNull('deleted_at')
                ->orderByDesc('versao')
                ->first(['id', 'versao', 'vigencia_inicio']);

            if (!$vigente) {
                return ['vigente' => null, 'riscos' => []];
            }

            return [
                'vigente' => ['id' => (int) $vigente->id, 'versao' => (int) $vigente->versao, 'vigencia_inicio' => $vigente->vigencia_inicio],
                'riscos' => $this->riscosPorCargoDoInventario((int) $vigente->id),
            ];
        });
    }

    private function versao(int $empresaId): int
    {
        return (int) Cache::get($this->chaveVersao($empresaId), 1);
    }

    private function chaveVersao(int $empresaId): string
    {
        return 'saude_ocupacional:pgr:versao:' . $empresaId;
    }
}
