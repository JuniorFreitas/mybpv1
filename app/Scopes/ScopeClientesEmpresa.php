<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Isola por cliente_id usando o tenant da empresa (ClientesEmpresa).
 *
 * O pivot cliente_funcionarios só restringe a um cliente quando esse ID
 * pertence a ClientesEmpresa — pivot sujo/órfão não pode vazar outro tenant.
 */
class ScopeClientesEmpresa implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        $user = auth()->user();

        if (!$user) {
            if (app()->runningInConsole()) {
                return;
            }

            $builder->whereRaw('0 = 1');

            return;
        }

        $idsPermitidos = $user->ClientesEmpresa->pluck('id');

        if ($idsPermitidos->isEmpty()) {
            $builder->whereRaw('0 = 1');

            return;
        }

        $clientePivot = $user->ClienteFuncionarios->first();

        if ($clientePivot && $idsPermitidos->contains($clientePivot->id)) {
            $builder->where('cliente_id', $clientePivot->id);

            return;
        }

        $builder->whereIn('cliente_id', $idsPermitidos);
    }
}
