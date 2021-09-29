<?php

namespace App\Tenant\Observers;

use Illuminate\Database\Eloquent\Model;

class EmpresaObserver
{
    public function creating(Model $model)
    {
        $model->setAttribute('empresa_id', auth()->user()->empresa_id);
    }

    public function updating(Model $model)
    {
        $model->setAttribute('empresa_id', auth()->user()->empresa_id);
    }
}
