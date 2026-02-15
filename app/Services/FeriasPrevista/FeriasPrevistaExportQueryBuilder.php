<?php

namespace App\Services\FeriasPrevista;

use App\Models\Ferias;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class FeriasPrevistaExportQueryBuilder
{
    public static function forExport(User $user, array $filtros = []): Builder
    {
        $query = Ferias::with([
            'PeriodoAquisitivo:id,label,ano_inicial,ano_final',
            'Gestor:id,nome',
            'GestorAprovacao:id,nome',
            'AprovacaoExtra:id,nome',
            'RhAprovacao:id,nome',
            'Solicitante:id,nome',
            'Admissao:id,centro_custo_id,cargo,funcao,data_admissao,feedback_id',
            'Admissao.CentroCusto:id,label',
            'Admissao.Feedback:id,curriculo_id,vagas_abertas_id',
            'Admissao.Feedback.VagaSelecionada',
            'Admissao.Feedback.Curriculo:id,nome,nascimento,rg,orgao_expeditor',
            'FeriasPrevista:id,centro_custo_id',
            'FeriasPrevista.CentroCusto:id,label',
        ])->where('empresa_id', $user->empresa_id);

        (new FeriasPrevistaFilterApplier($filtros, $user))->apply($query);
        return $query;
    }
}
