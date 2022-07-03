<?php

namespace App\Tenant\Observers;

use Illuminate\Database\Eloquent\Model;

class EmpresaObserver
{
    /**
     * @param Model $model
     * @return void
     */
    public function creating(Model $model)
    {
        $empresa_id = auth()->check() ? auth()->user()->empresa_id : $model->empresa_id;
        $model->setAttribute('empresa_id', $empresa_id);
    }

    /**
     * @param Model $model
     * @return void
     */
    public function updating(Model $model)
    {
        $empresa_id = auth()->check() ? auth()->user()->empresa_id : $model->empresa_id;
        $model->setAttribute('empresa_id', $empresa_id);
    }
}
