<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('cliente_configs', 'modulo_saude_ocupacional')) {
                $table->boolean('modulo_saude_ocupacional')
                    ->default(false)
                    ->after('assinatura_digital_habilitada');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            if (Schema::hasColumn('cliente_configs', 'modulo_saude_ocupacional')) {
                $table->dropColumn('modulo_saude_ocupacional');
            }
        });
    }
};
