<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aniversariante_whatsapp_mensagens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedTinyInteger('dia');
            $table->longText('corpo');
            $table->timestamps();

            $table->unique(['empresa_id', 'dia'], 'aniversariante_wpp_msg_empresa_dia_uidx');
            $table->foreign('empresa_id')->references('id')->on('clientes')->cascadeOnDelete();
        });

        Schema::table('cliente_configs', function (Blueprint $table) {
            $table->boolean('aniversario_whatsapp')->default(false)->after('envia_whatsapp');
        });

        Schema::table('parabens_enviados', function (Blueprint $table) {
            $table->string('whatsapp_status', 20)->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('parabens_enviados', function (Blueprint $table) {
            $table->dropColumn('whatsapp_status');
        });

        Schema::table('cliente_configs', function (Blueprint $table) {
            $table->dropColumn('aniversario_whatsapp');
        });

        Schema::dropIfExists('aniversariante_whatsapp_mensagens');
    }
};
