<?php

namespace App\Services\MudancaCargo;

use App\Models\MudancaCargo;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class MudancaCargoExportQueryBuilder
{
    public static function forExport(User $user, array $filtros = []): Builder
    {
        $query = MudancaCargo::with([
            'CentroCustoAnterior:id,label',
            'CentroCustoNovo:id,label',
            'CentroCustoFilialAnterior:id,cliente_filial_id',
            'CentroCustoFilialAnterior.Filial:id,dados',
            'CentroCustoFilialNovo:id,cliente_filial_id',
            'CentroCustoFilialNovo.Filial:id,dados',
            'VagaAbertaAnterior.Vaga:id,nome',
            'VagaAbertaNova.Vaga:id,nome',
            'Solicitante:id,nome',
            'GestorAprovacao:id,nome',
            'Gestor:id,nome',
            'AprovacaoExtra:id,nome',
            'RhAprovacao:id,nome',
            'Colaborador:id,nome',
            'Admissao:id,feedback_id',
            'Admissao.Feedback:id,curriculo_id',
            'Admissao.Feedback.Curriculo:id,nome',
        ])->where('empresa_id', $user->empresa_id);

        (new MudancaCargoFilterApplier($filtros, $user))->apply($query);
        return $query;
    }
}
