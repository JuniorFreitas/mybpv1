<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeedbackCurriculosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('feedback_curriculos', function (Blueprint $table) {
            $table->unsignedBigInteger('curriculo_id')->index('feedback_curriculos_curriculo_id_foreign');
            $table->bigIncrements('id');
            $table->string('selecionado')->nullable();
            $table->unsignedBigInteger('vaga_id')->nullable()->index('feedback_curriculos_vaga_id_foreign');
            $table->unsignedBigInteger('usuario_entrevista_marcado')->nullable()->index('feedback_curriculos_usuario_entrevista_marcado_foreign');
            $table->unsignedBigInteger('cliente_id')->nullable()->index('feedback_curriculos_cliente_id_foreign');
            $table->boolean('contato_realizado')->nullable();
            $table->boolean('interesse')->nullable();
            $table->dateTime('data_entrevista')->nullable();
            $table->string('local_entrevista', 255)->nullable();
            $table->unsignedBigInteger('telefone_id')->nullable()->index('feedback_curriculos_telefone_id_foreign');
            $table->text('obs')->nullable();
            $table->timestamps();
            $table->string('status')->nullable();
            $table->boolean('envia_mail_provas')->nullable();
            $table->dateTime('data_envia_mail_provas')->nullable();
            $table->unsignedBigInteger('user_envia_mail_provas')->nullable()->index('feedback_curriculos_user_envia_mail_provas_foreign');
            $table->boolean('envia_mail_proxima_etapa')->nullable();
            $table->dateTime('data_envia_mail_proxima_etapa')->nullable();
            $table->unsignedBigInteger('user_envia_mail_proxima_etapa')->nullable()->index('feedback_curriculos_user_envia_mail_proxima_etapa_foreign');
            $table->boolean('envia_mail_desclassificacao')->nullable();
            $table->dateTime('data_envia_mail_desclassificacao')->nullable();
            $table->unsignedBigInteger('user_envia_mail_desclassificacao')->nullable()->index('feedback_curriculos_user_envia_mail_desclassificacao_foreign');
            $table->boolean('envia_whatsapp')->nullable();
            $table->dateTime('data_envia_whatsapp')->nullable();
            $table->unsignedBigInteger('user_envia_whatsapp')->nullable()->index('feedback_curriculos_user_envia_whatsapp_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('feedback_curriculos');
    }
}
