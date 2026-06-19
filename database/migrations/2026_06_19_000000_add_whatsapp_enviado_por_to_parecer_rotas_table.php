<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parecer_rotas', function (Blueprint $table) {
            $table->unsignedBigInteger('whatsapp_enviado_por')
                ->nullable()
                ->after('whatsapp_enviado_em')
                ->index('parecer_rotas_whatsapp_enviado_por_foreign');

            $table->foreign('whatsapp_enviado_por', 'parecer_rotas_whatsapp_enviado_por_foreign')
                ->references('id')
                ->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('SET NULL');
        });
    }

    public function down(): void
    {
        Schema::table('parecer_rotas', function (Blueprint $table) {
            $table->dropForeign('parecer_rotas_whatsapp_enviado_por_foreign');
            $table->dropColumn('whatsapp_enviado_por');
        });
    }
};
