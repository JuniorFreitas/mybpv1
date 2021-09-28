<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAvaliacaoAnualFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('avaliacao_anual_feedbacks', function (Blueprint $table) {
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('pergunta_id')->references('id')->on('formulario_avaliacao_anuals')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('avaliacao_anual_feedbacks', function (Blueprint $table) {
            $table->dropForeign('avaliacao_anual_feedbacks_feedback_id_foreign');
            $table->dropForeign('avaliacao_anual_feedbacks_pergunta_id_foreign');
        });
    }
}
