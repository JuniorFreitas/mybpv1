<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Gleidilene (user 40283) tinha uma 2ª conta na CMPC (id 82394, Papel 71
     * "BPSE - ADM", sem habilidades de controle_ponto) antes do dedup da
     * Fase 1. A concessão de acesso da Fase 3 (migration 100012) não trouxe
     * esse papel junto — corrige aqui pra ela ter as habilidades certas ao
     * trocar pra CMPC como empresa ativa.
     */
    public function up(): void
    {
        DB::table('user_empresas')
            ->where('user_id', 40283)
            ->where('empresa_id', 40568)
            ->update(['papel_id' => 71]);
    }

    public function down(): void
    {
        DB::table('user_empresas')
            ->where('user_id', 40283)
            ->where('empresa_id', 40568)
            ->update(['papel_id' => null]);
    }
};
