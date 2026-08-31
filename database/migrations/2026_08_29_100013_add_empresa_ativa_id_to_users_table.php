<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 4 — "empresa ativa" da sessão do usuário (troca de workspace).
     * NULL por padrão: quem só tem 1 empresa nunca precisa disso, o sistema
     * cai no fallback users.empresa_id (zero mudança de comportamento).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_ativa_id')->nullable()->after('empresa_id');
            $table->foreign('empresa_ativa_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['empresa_ativa_id']);
            $table->dropColumn('empresa_ativa_id');
        });
    }
};
