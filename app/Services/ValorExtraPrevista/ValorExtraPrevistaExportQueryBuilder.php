<?php

namespace App\Services\ValorExtraPrevista;

use App\Models\User;
use App\Models\ValorExtraPrevista;
use Illuminate\Database\Eloquent\Builder;

class ValorExtraPrevistaExportQueryBuilder
{
    public static function forExport(User $user, array $filtros = []): Builder
    {
        $query = ValorExtraPrevista::with([
            'CentroCusto:id,label',
            'CentroCustoFilial:id,cliente_filial_id',
            'CentroCustoFilial.Filial:id,dados',
            'UserCadastrou:id,nome',
            'Colaborador:id,nome',
            'Colaborador.Feedback:id,curriculo_id,vagas_abertas_id',
            'Colaborador.Feedback.VagaAberta:id,vaga_id',
            'Colaborador.Feedback.VagaAberta.Vaga:id,nome',
            'GestorAprovacao:id,nome',
            'UserAprovacao:id,nome',
            'RhAprovacao:id,nome',
            'AprovacaoExtra:id,nome',
        ])->where('empresa_id', $user->empresa_id)->whereNull('deleted_at');

        (new ValorExtraPrevistaFilterApplier($filtros, $user))->apply($query);
        return $query;
    }
}
