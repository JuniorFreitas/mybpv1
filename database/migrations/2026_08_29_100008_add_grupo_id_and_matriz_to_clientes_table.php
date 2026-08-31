<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 2 — grupo passa a ser o agrupador de N empresas (substituindo o
     * conceito de "filial" a médio prazo, ver database/migrations
     * 2026_08_29_10000{0..6} para o histórico da Fase 1). `matriz` marca qual
     * empresa do grupo é a principal; um grupo com 1 empresa só, essa empresa
     * é matriz por definição.
     */
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->unsignedBigInteger('grupo_id')->nullable()->after('id');
            $table->boolean('matriz')->default(true)->after('grupo_id');

            $table->foreign('grupo_id')->references('id')->on('grupos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['grupo_id']);
            $table->dropColumn(['grupo_id', 'matriz']);
        });
    }
};
