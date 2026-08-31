<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 5 (início) — rastreia qual `clientes`/`users` novo (empresa,
     * matriz=false) corresponde a cada filial antiga, sem tocar nos 184
     * usos de filial_id no restante do sistema ainda. Migração de código
     * fica para depois, módulo por módulo, com compat.
     */
    public function up(): void
    {
        Schema::table('cliente_filials', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_nova_id')->nullable()->after('empresa_id');
            $table->foreign('empresa_nova_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cliente_filials', function (Blueprint $table) {
            $table->dropForeign(['empresa_nova_id']);
            $table->dropColumn('empresa_nova_id');
        });
    }
};
