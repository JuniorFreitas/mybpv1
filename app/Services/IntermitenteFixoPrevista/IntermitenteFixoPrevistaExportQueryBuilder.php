<?php

namespace App\Services\IntermitenteFixoPrevista;

use App\Models\IntermitenteFixoPrevista;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class IntermitenteFixoPrevistaExportQueryBuilder
{
    public static function forExport(User $user, array $filtros = []): Builder
    {
        $query = IntermitenteFixoPrevista::with([
            'CentroCusto:id,label',
            'CentroCustoFilial:id',
            'CentroCustoFilial.Filial:id,dados',
            'AreaEtiqueta:id,label',
            'NovoCargo:id,nome',
            'VagaAbertaAnterior:id,titulo,vaga_id',
            'VagaAbertaNova:id,titulo,vaga_id',
            'UserAprovacao:id,nome',
            'UserCadastrou:id,nome',
            'GestorAprovacao:id,nome',
            'RhAprovacao:id,nome',
            'Colaborador:id,nome',
        ])->where('empresa_id', $user->empresa_id)->whereNull('deleted_at');

        (new IntermitenteFixoPrevistaFilterApplier($filtros, $user))->apply($query);
        return $query;
    }
}
