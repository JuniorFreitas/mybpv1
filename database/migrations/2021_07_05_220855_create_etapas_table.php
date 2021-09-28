<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEtapasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('etapas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable()->index('etapas_feedback_id_foreign');
            $table->unsignedBigInteger('user_id')->index('etapas_user_id_foreign');
            $table->unsignedBigInteger('vaga_id')->index('etapas_vaga_id_foreign');
            $table->string('etapa');
            $table->boolean('enviado_email')->nullable();
            $table->longText('text_email')->nullable();
            $table->longText('observacao')->nullable();
            $table->string('status')->comment('classificado,desclassificado,andamento');
            $table->string('preenchido_por')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('passo_id')->nullable()->index('etapas_passo_id_foreign')->comment('é o id da etapa_tipo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('etapas');
    }
}
