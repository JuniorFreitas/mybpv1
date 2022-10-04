<?php

namespace App\Tenant;

use App\Models\Cliente;

class ManagerTenant
{
    public function getTenantIdentify()
    {
        return auth()->user()->empresa_id;
    }

    public function getTenant(): Cliente
    {
        return auth()->user()->Empresa;
    }
}
