<?php

namespace App\Services\RequisicaoVaga;

use App\Models\RequisicaoVagaMovimentacao;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Query builder para Requisição de Vaga.
 * Usa o mesmo RequisicaoVagaFilterApplier da listagem = filtro inteiro igual (padrão CIH).
 */
class RequisicaoVagaExportQueryBuilder
{
    public static function forExport(User $user, array $filtros = []): Builder
    {
        $query = RequisicaoVagaMovimentacao::with([
            'CentroCusto:id,label',
            'Cargo:id,nome',
            'Area:id,label',
            'UserCadastrou:id,nome',
            'UserAprovacao:id,nome',
            'AprovacaoExtra:id,nome',
            'AprovacaoRh:id,nome',
            'GestorContratacao:id,nome',
        ])->where('empresa_id', $user->empresa_id);

        $filterApplier = new RequisicaoVagaFilterApplier($filtros, $user);
        $filterApplier->apply($query);

        return $query;
    }
}
