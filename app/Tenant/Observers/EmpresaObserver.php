<?php

namespace App\Tenant\Observers;

use Illuminate\Database\Eloquent\Model;

class EmpresaObserver
{
    public function creating(Model $model)
    {
        if (auth()->check()) {
            $model->setAttribute('empresa_id', auth()->user()->empresa_id);
        }else{
            $model->setAttribute('empresa_id', $model->empresa_id);
        }
//        $model->setAttribute('cliente_id', auth()->user()->empresa_id);
    }

    public function updating(Model $model)
    {
        if (auth()->check()) {
            $model->setAttribute('empresa_id', auth()->user()->empresa_id);
        }else{
            $model->setAttribute('empresa_id', $model->empresa_id);
        }//        $model->setAttribute('cliente_id', auth()->user()->empresa_id);
    }
}
