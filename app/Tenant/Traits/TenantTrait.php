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

        // Laravel 13: observe() instancia o model — não pode rodar durante o boot.
        static::whenBooted(static fn () => static::observe(EmpresaObserver::class));
    }
}
