<?php

namespace App\Services\TransferenciaPrevista;

use App\Models\TransferenciaPrevista;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class TransferenciaPrevistaExportQueryBuilder
{
    public static function forExport(User $user, array $filtros = []): Builder
    {
        $query = TransferenciaPrevista::with([
            'CentroCustoOrigem:id,label',
            'CentroCustoDestino:id,label',
            'QuemAprovou:id,nome',
            'UserCadastrou:id,nome',
            'GestorAprovacao:id,nome',
            'Colaborador:id,nome',
            'UserAprovacao:id,nome',
            'UserAprovacaoExtra:id,nome',
            'RhAprovacao:id,nome',
        ])->where('empresa_id', $user->empresa_id);

        (new TransferenciaPrevistaFilterApplier($filtros, $user))->apply($query);
        return $query;
    }
}
