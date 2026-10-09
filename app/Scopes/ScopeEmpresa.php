<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ScopeEmpresa implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * Fail-closed: sem usuário autenticado a query não retorna dados de tenant.
     * Jobs/console devem usar withoutGlobalScopes() (ou autenticar contexto) quando
     * precisarem cruzar empresas.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $user = auth()->user();

        if ($model->hasCast('empresa_id')) { // pro nao aceitar Nome de classe statico, exemplo:  Curriculo:get();
            if ($user) {
                $builder->where($model->getTable() . '.empresa_id', $user->empresa_id);

                return;
            }

            $builder->whereRaw('1 = 0');

            return;
        }

        if (!$user) {
            $builder->whereRaw('1 = 0');

            return;
        }

        $builder->whereHas('Pessoa', function ($query) use ($user) {
            $query->whereEmpresaId($user->empresa_id);
        });
    }
}
