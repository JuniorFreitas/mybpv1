<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('cliente_configs', 'assinatura_digital_habilitada')) {
                $table->boolean('assinatura_digital_habilitada')
                    ->default(false)
                    ->after('supervisor_etiqueta_bloqueio');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            if (Schema::hasColumn('cliente_configs', 'assinatura_digital_habilitada')) {
                $table->dropColumn('assinatura_digital_habilitada');
            }
        });
    }
};

