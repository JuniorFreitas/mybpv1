<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('cliente_configs', 'mfa_login_habilitado')) {
                $table->boolean('mfa_login_habilitado')->default(false)->after('envia_whatsapp');
            }
            if (!Schema::hasColumn('cliente_configs', 'mfa_login_email')) {
                $table->boolean('mfa_login_email')->default(true)->after('mfa_login_habilitado');
            }
            if (!Schema::hasColumn('cliente_configs', 'mfa_login_whatsapp')) {
                $table->boolean('mfa_login_whatsapp')->default(false)->after('mfa_login_email');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cliente_configs', function (Blueprint $table) {
            foreach (['mfa_login_whatsapp', 'mfa_login_email', 'mfa_login_habilitado'] as $coluna) {
                if (Schema::hasColumn('cliente_configs', $coluna)) {
                    $table->dropColumn($coluna);
                }
            }
        });
    }
};
