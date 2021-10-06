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
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        if ($model->hasCast('empresa_id')) { // pro nao aceitar Nome de classe statico, exemplo:  Curriculo:get();
            if(auth()->user()){
                return $builder->where('empresa_id', auth()->user()->empresa_id);
            }
        }

      /*  if ($model->hasCast('cliente_id')) { // pro nao aceitar Nome de classe statico, exemplo:  Curriculo:get();
            if(auth()->user()){
                return $builder->where('cliente_id', auth()->user()->empresa_id);
            }
        } */
        else {
            return $builder->whereHas('Pessoa', function ($query) {
                $query->whereEmpresaId(auth()->user()->empresa_id);
            });
        }
    }
}
