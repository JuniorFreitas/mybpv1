<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Acesso público aos documentos de pré-admissão: link por `public_id` + código de uso único.
     * O `public_id` é gerado sob demanda (ao montar o link), por isso não há backfill.
     */
    public function up(): void
    {
        Schema::table('resultado_integrados', function (Blueprint $table) {
            if (!Schema::hasColumn('resultado_integrados', 'public_id')) {
                $table->uuid('public_id')->nullable()->unique()->after('feedback_id');
            }
            if (!Schema::hasColumn('resultado_integrados', 'acesso_codigo_hash')) {
                $table->string('acesso_codigo_hash', 255)->nullable()->after('public_id');
            }
            if (!Schema::hasColumn('resultado_integrados', 'acesso_codigo_expira_em')) {
                $table->timestamp('acesso_codigo_expira_em')->nullable()->after('acesso_codigo_hash');
            }
            if (!Schema::hasColumn('resultado_integrados', 'acesso_codigo_usado_em')) {
                $table->timestamp('acesso_codigo_usado_em')->nullable()->after('acesso_codigo_expira_em');
            }
        });
    }

    public function down(): void
    {
        Schema::table('resultado_integrados', function (Blueprint $table) {
            if (Schema::hasColumn('resultado_integrados', 'public_id')) {
                $table->dropUnique(['public_id']);
            }
            foreach (['acesso_codigo_usado_em', 'acesso_codigo_expira_em', 'acesso_codigo_hash', 'public_id'] as $col) {
                if (Schema::hasColumn('resultado_integrados', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
