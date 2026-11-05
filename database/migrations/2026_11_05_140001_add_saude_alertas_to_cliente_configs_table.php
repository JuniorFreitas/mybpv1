<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Alertas de exames periódicos (Saúde Ocupacional): marcos em dias antes do vencimento (ex.: [60, 30, 7])
 * e resumo semanal por e-mail. null em saude_alerta_janelas = padrão [60, 30, 7].
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('cliente_configs', 'saude_alerta_janelas')) {
                $table->json('saude_alerta_janelas')->nullable();
            }
            if (!Schema::hasColumn('cliente_configs', 'saude_resumo_semanal')) {
                $table->boolean('saude_resumo_semanal')->default(true);
            }
        });
    }

    public function down(): void
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            foreach (['saude_alerta_janelas', 'saude_resumo_semanal'] as $coluna) {
                if (Schema::hasColumn('cliente_configs', $coluna)) {
                    $table->dropColumn($coluna);
                }
            }
        });
    }
};
