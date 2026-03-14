<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Campo JSON para novas configurações (ex.: treinamento_fat_obrigatorio), evitando crescimento da tabela.
     */
    public function up(): void
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            $table->json('configuracoes')->nullable()->after('assinatura_exibir_cpf_completo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            $table->dropColumn('configuracoes');
        });
    }
};
