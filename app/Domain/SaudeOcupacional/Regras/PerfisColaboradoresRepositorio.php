<?php

namespace App\Domain\SaudeOcupacional\Regras;

use App\Domain\SaudeOcupacional\Pgr\PgrVigenteRepositorio;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Carrega nascimento, cargo vigente e riscos (PGR vigente) dos colaboradores em lote.
 * Cargo vigente: vagas_abertas.vaga_id (atualizado na mudança de cargo aprovada) ou feedback.vaga_id.
 */
class PerfisColaboradoresRepositorio
{
    private const LOTE = 1000;

    public function __construct(private readonly PgrVigenteRepositorio $pgr = new PgrVigenteRepositorio())
    {
    }

    public function carregar(int $feedbackId): ?PerfilColaborador
    {
        return $this->carregarVarios([$feedbackId])[$feedbackId] ?? null;
    }

    /**
     * @param  list<int>  $feedbackIds
     * @return array<int, PerfilColaborador>
     */
    public function carregarVarios(array $feedbackIds): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $feedbackIds))));
        $perfis = [];
        $riscosPorEmpresa = [];

        foreach (array_chunk($ids, self::LOTE) as $lote) {
            $linhas = DB::table('feedback_curriculos as fc')
                ->leftJoin('curriculos as c', 'c.id', '=', 'fc.curriculo_id')
                ->leftJoin('vagas_abertas as va', 'va.id', '=', 'fc.vagas_abertas_id')
                ->whereIn('fc.id', $lote)
                ->get(['fc.id', 'fc.empresa_id', 'c.nascimento', 'va.vaga_id as vaga_vigente_id', 'fc.vaga_id']);

            foreach ($linhas as $linha) {
                $empresaId = (int) $linha->empresa_id;
                $riscosPorEmpresa[$empresaId] ??= $this->pgr->riscosPorCargo($empresaId);
                $vagaId = $linha->vaga_vigente_id ? (int) $linha->vaga_vigente_id : ($linha->vaga_id ? (int) $linha->vaga_id : null);

                $perfis[(int) $linha->id] = new PerfilColaborador(
                    (int) $linha->id,
                    $this->data($linha->nascimento),
                    $vagaId,
                    $vagaId ? ($riscosPorEmpresa[$empresaId][$vagaId] ?? []) : []
                );
            }
        }

        return $perfis;
    }

    private function data(?string $valor): ?CarbonImmutable
    {
        if (!$valor || str_starts_with($valor, '0000')) {
            return null;
        }

        try {
            return CarbonImmutable::parse($valor)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
