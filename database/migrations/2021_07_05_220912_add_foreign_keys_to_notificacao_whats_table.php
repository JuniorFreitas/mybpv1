<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToNotificacaoWhatsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notificacao_whats', function (Blueprint $table) {
            $table->foreign('etapa_id')->references('id')->on('etapas')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('vaga_id')->references('id')->on('vagas')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notificacao_whats', function (Blueprint $table) {
            $table->dropForeign('notificacao_whats_etapa_id_foreign');
            $table->dropForeign('notificacao_whats_feedback_id_foreign');
            $table->dropForeign('notificacao_whats_user_id_foreign');
            $table->dropForeign('notificacao_whats_vaga_id_foreign');
        });
    }
}
