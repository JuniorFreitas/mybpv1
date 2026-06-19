<?php

namespace App\Observers;

use App\Models\Arquivo;
use App\Support\IntegracaoSpa\IntegracaoSpaEmpresasAtivasCache;
use Illuminate\Support\Facades\DB;

/**
 * Invalida o cache da listagem SPA quando um arquivo usado como logotipo
 * de alguma empresa é atualizado ou removido.
 */
class ArquivoLogotipoIntegracaoSpaEmpresasAtivasCacheObserver
{
    public function updated(Arquivo $arquivo): void
    {
        $this->forgetIfLogotipo($arquivo->id);
    }

    /**
     * Usa `deleting` (e não `deleted`): com ON DELETE CASCADE na pivot, após
     * remover o arquivo a linha em cliente_logotipo já não existe.
     */
    public function deleting(Arquivo $arquivo): void
    {
        $this->forgetIfLogotipo($arquivo->id);
    }

    private function forgetIfLogotipo(int $arquivoId): void
    {
        $exists = DB::table('cliente_logotipo')
            ->where('arquivo_id', $arquivoId)
            ->exists();

        if ($exists) {
            IntegracaoSpaEmpresasAtivasCache::forget();
        }
    }
}
