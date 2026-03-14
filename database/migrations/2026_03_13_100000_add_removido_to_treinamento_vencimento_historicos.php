<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * True quando o registro de histórico refere-se a um vencimento que foi alterado para "não" (desmarcado).
     */
    public function up(): void
    {
        Schema::table('treinamento_vencimento_historicos', function (Blueprint $table) {
            $table->boolean('removido')->default(false)->after('vencimento_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treinamento_vencimento_historicos', function (Blueprint $table) {
            $table->dropColumn('removido');
        });
    }
};
