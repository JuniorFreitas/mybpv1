<?php

namespace App\Tenant\Traits;

use App\Tenant\Observers\EmpresaObserver;
use App\Tenant\Scopes\ScopeEmpresa;

trait TenantTrait
{
    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new ScopeEmpresa());
        static::observe(new EmpresaObserver());
    }
}
