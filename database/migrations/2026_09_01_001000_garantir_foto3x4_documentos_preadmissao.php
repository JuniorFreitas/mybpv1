<?php

use App\Services\Preadmissao\DocumentoPreadmissaoCadastroService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clientes') || !Schema::hasTable('documentos_curriculos_adm_empresa')) {
            return;
        }

        $service = app(DocumentoPreadmissaoCadastroService::class);

        DB::table('clientes')->orderBy('id')->chunkById(500, function ($empresas) use ($service) {
            foreach ($empresas as $empresa) {
                $service->garantirPadraoSistema((int) $empresa->id);
            }
        });
    }

    public function down(): void
    {
        // Documento padrão do sistema permanece nas empresas.
    }
};
