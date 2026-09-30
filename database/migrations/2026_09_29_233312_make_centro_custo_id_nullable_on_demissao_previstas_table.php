<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite demissão quando o colaborador não possui centro de custo cadastrado.
     */
    public function up(): void
    {
        Schema::table('demissao_previstas', function (Blueprint $table) {
            $table->dropForeign(['centro_custo_id']);
        });

        Schema::table('demissao_previstas', function (Blueprint $table) {
            $table->unsignedBigInteger('centro_custo_id')->nullable()->change();
        });

        Schema::table('demissao_previstas', function (Blueprint $table) {
            $table->foreign('centro_custo_id')
                ->references('id')
                ->on('centro_custos')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('demissao_previstas', function (Blueprint $table) {
            $table->dropForeign(['centro_custo_id']);
        });

        Schema::table('demissao_previstas', function (Blueprint $table) {
            $table->unsignedBigInteger('centro_custo_id')->nullable(false)->change();
        });

        Schema::table('demissao_previstas', function (Blueprint $table) {
            $table->foreign('centro_custo_id')
                ->references('id')
                ->on('centro_custos')
                ->cascadeOnDelete();
        });
    }
};
