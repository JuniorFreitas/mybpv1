<?php

namespace App\Observers;

use App\Models\Cliente;
use App\Support\IntegracaoSpa\IntegracaoSpaEmpresasAtivasCache;

/**
 * Invalida o cache da listagem pública de empresas (integração SPA v2) quando
 * o cadastro da empresa é alterado via Eloquent.
 */
class ClienteIntegracaoSpaEmpresasAtivasCacheObserver
{
    public function saved(Cliente $cliente): void
    {
        IntegracaoSpaEmpresasAtivasCache::forget();
    }

    public function deleted(Cliente $cliente): void
    {
        IntegracaoSpaEmpresasAtivasCache::forget();
    }
}
