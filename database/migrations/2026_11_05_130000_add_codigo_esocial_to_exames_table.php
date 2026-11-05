<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catálogo de exames complementares (tabela `exames`, reaproveitada): código do procedimento
 * na Tabela 27 do eSocial (para o S-2220 no futuro).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exames', function (Blueprint $table) {
            if (!Schema::hasColumn('exames', 'codigo_esocial')) {
                $table->string('codigo_esocial', 10)->nullable()->after('label');
            }
        });
    }

    public function down(): void
    {
        Schema::table('exames', function (Blueprint $table) {
            if (Schema::hasColumn('exames', 'codigo_esocial')) {
                $table->dropColumn('codigo_esocial');
            }
        });
    }
};
