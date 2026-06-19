<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parecer_rotas', function (Blueprint $table) {
            $table->timestamp('whatsapp_enviado_em')->nullable()->after('observacao');
        });
    }

    public function down(): void
    {
        Schema::table('parecer_rotas', function (Blueprint $table) {
            $table->dropColumn('whatsapp_enviado_em');
        });
    }
};
