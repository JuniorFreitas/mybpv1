<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAvaliacaoNoventaFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('avaliacao_noventa_feedbacks', function (Blueprint $table) {
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('gestor_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('pergunta_id')->references('id')->on('formulario_avaliacao_noventa')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('avaliacao_noventa_feedbacks', function (Blueprint $table) {
            $table->dropForeign('avaliacao_noventa_feedbacks_feedback_id_foreign');
            $table->dropForeign('avaliacao_noventa_feedbacks_gestor_id_foreign');
            $table->dropForeign('avaliacao_noventa_feedbacks_pergunta_id_foreign');
        });
    }
}
