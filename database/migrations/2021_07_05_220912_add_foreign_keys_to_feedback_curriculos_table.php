<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToFeedbackCurriculosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feedback_curriculos', function (Blueprint $table) {
            $table->foreign('cliente_id')->references('id')->on('clientes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('curriculo_id')->references('id')->on('curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('telefone_id')->references('id')->on('curriculo_telefone')->onUpdate('RESTRICT')->onDelete('SET NULL');
            $table->foreign('user_envia_mail_desclassificacao')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('user_envia_mail_provas')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('user_envia_mail_proxima_etapa')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('user_envia_whatsapp')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('usuario_entrevista_marcado')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('vaga_id')->references('id')->on('vagas')->onUpdate('RESTRICT')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('feedback_curriculos', function (Blueprint $table) {
            $table->dropForeign('feedback_curriculos_cliente_id_foreign');
            $table->dropForeign('feedback_curriculos_curriculo_id_foreign');
            $table->dropForeign('feedback_curriculos_telefone_id_foreign');
            $table->dropForeign('feedback_curriculos_user_envia_mail_desclassificacao_foreign');
            $table->dropForeign('feedback_curriculos_user_envia_mail_provas_foreign');
            $table->dropForeign('feedback_curriculos_user_envia_mail_proxima_etapa_foreign');
            $table->dropForeign('feedback_curriculos_user_envia_whatsapp_foreign');
            $table->dropForeign('feedback_curriculos_usuario_entrevista_marcado_foreign');
            $table->dropForeign('feedback_curriculos_vaga_id_foreign');
        });
    }
}
