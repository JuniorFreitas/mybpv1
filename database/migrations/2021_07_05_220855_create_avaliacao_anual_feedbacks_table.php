<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvaliacaoAnualFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avaliacao_anual_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->index('avaliacao_anual_feedbacks_feedback_id_foreign');
            $table->unsignedBigInteger('pergunta_id')->index('avaliacao_anual_feedbacks_pergunta_id_foreign');
            $table->integer('nota');
            $table->integer('quantidade_avaliacao');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avaliacao_anual_feedbacks');
    }
}
