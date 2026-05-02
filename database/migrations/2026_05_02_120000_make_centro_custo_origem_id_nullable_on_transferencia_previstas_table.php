<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permite transferência quando o colaborador não possui centro de custo de origem cadastrado.
     */
    public function up(): void
    {
        Schema::table('transferencia_previstas', function (Blueprint $table) {
            $table->dropForeign(['centro_custo_origem_id']);
        });

        Schema::table('transferencia_previstas', function (Blueprint $table) {
            $table->unsignedBigInteger('centro_custo_origem_id')->nullable()->change();
        });

        Schema::table('transferencia_previstas', function (Blueprint $table) {
            $table->foreign('centro_custo_origem_id')
                ->references('id')
                ->on('centro_custos')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('transferencia_previstas', function (Blueprint $table) {
            $table->dropForeign(['centro_custo_origem_id']);
        });

        Schema::table('transferencia_previstas', function (Blueprint $table) {
            $table->unsignedBigInteger('centro_custo_origem_id')->nullable(false)->change();
        });

        Schema::table('transferencia_previstas', function (Blueprint $table) {
            $table->foreign('centro_custo_origem_id')
                ->references('id')
                ->on('centro_custos')
                ->cascadeOnDelete();
        });
    }
};
