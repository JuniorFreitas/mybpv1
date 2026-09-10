<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('cliente_configs', 'descricao_vaga_ia_habilitada')) {
                $table->boolean('descricao_vaga_ia_habilitada')
                    ->default(false)
                    ->after('assinatura_digital_habilitada');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            if (Schema::hasColumn('cliente_configs', 'descricao_vaga_ia_habilitada')) {
                $table->dropColumn('descricao_vaga_ia_habilitada');
            }
        });
    }
};
