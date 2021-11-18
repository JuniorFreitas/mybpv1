<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class ScopeOrderFeedback implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return Builder
     */
    public function apply(Builder $builder, Model $model)
    {
        $builder->select('feedback_curriculos.*')
            ->join('curriculos', 'curriculos.id', '=', 'feedback_curriculos.curriculo_id')
            ->orderBy('curriculos.nome');

        return $builder;
    }
}
