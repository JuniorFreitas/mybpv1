<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAvaliacaoAnualFeedbackQuantidadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('avaliacao_anual_feedback_quantidades', function (Blueprint $table) {
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('gestor_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('avaliacao_anual_feedback_quantidades', function (Blueprint $table) {
            $table->dropForeign('avaliacao_anual_feedback_quantidades_feedback_id_foreign');
            $table->dropForeign('avaliacao_anual_feedback_quantidades_gestor_id_foreign');
        });
    }
}
