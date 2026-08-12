<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ata_reuniao_notificacao_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->boolean('usar_dias_uteis')->default(false);
            $table->unsignedTinyInteger('dias_antecedencia')->default(2);
            $table->time('horario_envio')->default('07:00:00');
            $table->string('timezone', 80)->default('America/Sao_Paulo');
            $table->boolean('incluir_gestor_copia')->default(false);
            $table->boolean('reenviar_no_vencimento')->default(true);
            $table->boolean('cobrar_apos_atraso')->default(true);
            $table->json('dias_escalonamento')->nullable();
            $table->timestamps();

            $table->unique('empresa_id', 'ata_notif_config_empresa_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ata_reuniao_notificacao_configs');
    }
};
