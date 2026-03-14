<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Quando preenchido, o registro de histórico refere-se apenas a este vencimento (alteração 1 a 1).
     */
    public function up(): void
    {
        Schema::table('treinamento_vencimento_historicos', function (Blueprint $table) {
            $table->unsignedBigInteger('vencimento_id')->nullable()->after('treinamento_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('treinamento_vencimento_historicos', function (Blueprint $table) {
            $table->dropColumn('vencimento_id');
        });
    }
};
