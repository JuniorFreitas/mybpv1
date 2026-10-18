<?php

namespace App\Tenant\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ScopeEmpresa implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * Fail-closed: guests em HTTP sem sessão não veem dados cross-tenant.
     * Console/fila: não aplica filtro (jobs usam withoutGlobalScopes ou setam tenant).
     */
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

        if ($model->hasCast('empresa_id')) {
            $builder->where('empresa_id', $user->empresa_id);
            return;
        }

        $builder->whereHas('Pessoa', function ($query) use ($user) {
            $query->whereEmpresaId($user->empresa_id);
        });
    }
}
