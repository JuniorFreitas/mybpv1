<?php

namespace App\Tenant\Traits;

use App\Tenant\Observers\EmpresaObserver;
use App\Tenant\Scopes\ScopeEmpresa;

trait TenantTrait
{
    /**
     * Laravel 12.74+/13: Model::observe() instancia `new static` e não pode
     * rodar durante o boot. Registramos os eventos do EmpresaObserver direto.
     */
    protected static function bootTenantTrait(): void
    {
        static::addGlobalScope(new ScopeEmpresa());

        static::registerModelEvent('creating', EmpresaObserver::class.'@creating');
        static::registerModelEvent('updating', EmpresaObserver::class.'@updating');
    }
}
