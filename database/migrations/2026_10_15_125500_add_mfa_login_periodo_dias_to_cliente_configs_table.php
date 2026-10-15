<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('cliente_configs')) {
            return;
        }

        Schema::table('cliente_configs', function (Blueprint $table) {
            if (!Schema::hasColumn('cliente_configs', 'mfa_login_periodo_dias')) {
                $after = Schema::hasColumn('cliente_configs', 'mfa_login_whatsapp')
                    ? 'mfa_login_whatsapp'
                    : (Schema::hasColumn('cliente_configs', 'mfa_login_habilitado')
                        ? 'mfa_login_habilitado'
                        : 'envia_whatsapp');

                $table->unsignedInteger('mfa_login_periodo_dias')->default(30)->after($after);
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('cliente_configs')) {
            return;
        }

        Schema::table('cliente_configs', function (Blueprint $table) {
            if (Schema::hasColumn('cliente_configs', 'mfa_login_periodo_dias')) {
                $table->dropColumn('mfa_login_periodo_dias');
            }
        });
    }
};
