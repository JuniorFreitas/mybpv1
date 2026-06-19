<?php

namespace App\Observers;

use App\Models\VagasAbertas;
use App\Support\IntegracaoSpa\IntegracaoSpaVagasAbertasPaginaCache;

/**
 * Invalida o cache de paginação GET …/vagas-abertas quando uma vaga aberta da empresa muda.
 */
class VagasAbertasIntegracaoSpaPaginaCacheObserver
{
    public function saved(VagasAbertas $vagasAbertas): void
    {
        $this->bumpIfEmpresa($vagasAbertas->empresa_id);
    }

    public function deleted(VagasAbertas $vagasAbertas): void
    {
        $this->bumpIfEmpresa($vagasAbertas->empresa_id);
    }

    private function bumpIfEmpresa(?int $empresaId): void
    {
        if ($empresaId !== null && $empresaId > 0) {
            IntegracaoSpaVagasAbertasPaginaCache::bumpEmpresa($empresaId);
        }
    }
}
