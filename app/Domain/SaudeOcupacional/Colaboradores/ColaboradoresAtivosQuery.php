<?php

namespace App\Domain\SaudeOcupacional\Colaboradores;

use App\Models\Admissao;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Colaboradores ativos da empresa: admissão ADMITIDO/PRONTO PARA ADMISSÃO, sem demissão
 * e sem exclusão lógica. Mesma regra do relatório de vencimento de ASO.
 * Alias da tabela base: `fc` (feedback_curriculos), admissão: `a`.
 */
class ColaboradoresAtivosQuery
{
    public function daEmpresa(int $empresaId): Builder
    {
        return DB::table('feedback_curriculos as fc')
            ->join('admissoes as a', 'a.feedback_id', '=', 'fc.id')
            ->where('fc.empresa_id', $empresaId)
            ->whereNull('fc.deleted_at')
            ->whereNull('a.deleted_at')
            ->whereIn('a.status', [
                Admissao::STATUS_ADMISSAO_ADMITIDO,
                Admissao::STATUS_ADMISSAO_PRONTOPARAADMISSAO,
            ])
            ->whereNotExists(function ($query) {
                $query->selectRaw('1')
                    ->from('demissaos as d')
                    ->whereColumn('d.feedback_id', 'fc.id');
            });
    }
}
