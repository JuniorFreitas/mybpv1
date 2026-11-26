<?php

namespace App\Tenant\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Espelho de {@see \App\Scopes\ScopeClientesEmpresa}.
 * Mantido para imports legados em App\Tenant; mesma regra de isolamento.
 */
class ScopeClientesEmpresa implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        (new \App\Scopes\ScopeClientesEmpresa())->apply($builder, $model);
    }
}
