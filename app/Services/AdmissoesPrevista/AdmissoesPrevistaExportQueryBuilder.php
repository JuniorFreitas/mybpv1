<?php

namespace App\Services\AdmissoesPrevista;

use App\Models\AdmissoesPrevista;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AdmissoesPrevistaExportQueryBuilder
{
    public static function forExport(User $user, array $filtros = []): Builder
    {
        $query = AdmissoesPrevista::with([
            'Cargo:id,nome',
            'Colaborador:id,nome',
            'CentroCusto:id,label',
            'CentroCustoFilial:id',
            'CentroCustoFilial.Filial:id,dados',
            'UserCadastrou:id,nome',
            'GestorAprovacao:id,nome',
            'UserAprovacao:id,nome',
            'UserAprovacaoExtra:id,nome',
            'RhAprovacao:id,nome',
        ])->where('empresa_id', $user->empresa_id);

        (new AdmissoesPrevistaFilterApplier($filtros, $user))->apply($query);
        return $query;
    }
}
