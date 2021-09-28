<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResultadoIntegradosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resultado_integrados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable()->index('resultado_integrados_feedback_id_foreign');
            $table->boolean('documentos_entregue');
            $table->date('documentos_entregue_data')->nullable();
            $table->boolean('encaminhado_exame');
            $table->date('encaminhado_exame_data')->nullable();
            $table->boolean('encaminhado_treinamento');
            $table->date('encaminhado_treinamento_data')->nullable();
            $table->boolean('excessao')->nullable();
            $table->string('autorizado_por')->nullable();
            $table->unsignedBigInteger('usuario_id')->index('resultado_integrados_usuario_id_foreign');
            $table->string('responsavel_envio');
            $table->text('obs')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('formulario_id')->nullable()->index('resultado_integrados_formulario_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('resultado_integrados');
    }
}
