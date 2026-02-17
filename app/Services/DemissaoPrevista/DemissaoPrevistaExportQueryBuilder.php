<?php

namespace App\Services\DemissaoPrevista;

use App\Models\DemissaoPrevista;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Query builder para exportação de Demissão Prevista.
 */
class DemissaoPrevistaExportQueryBuilder
{
    public static function forExport(User $user, array $filtros = []): Builder
    {
        $query = DemissaoPrevista::with([
            'Colaborador:id,nome',
            'Colaborador.Curriculo:id,nome',
            'Colaborador.Feedback:id,curriculo_id',
            'Colaborador.Feedback.Admissao:id,feedback_id,cargo,data_admissao',
            'CentroCusto:id,label',
            'CentroCustoFilial:id',
            'UserCadastrou:id,nome',
            'GestorAprovacao:id,nome',
            'UserAprovacao:id,nome',
            'RhAprovacao:id,nome',
            'AprovacaoExtra:id,nome',
        ])
            ->where('demissao_previstas.empresa_id', $user->empresa_id)
            ->whereNull('demissao_previstas.deleted_at');

        $filterApplier = new DemissaoPrevistaFilterApplier($filtros, $user);
        $filterApplier->apply($query);

        return $query;
    }
}
