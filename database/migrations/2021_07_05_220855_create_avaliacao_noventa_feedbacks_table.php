<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvaliacaoNoventaFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avaliacao_noventa_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->index('avaliacao_noventa_feedbacks_feedback_id_foreign');
            $table->unsignedBigInteger('pergunta_id')->index('avaliacao_noventa_feedbacks_pergunta_id_foreign');
            $table->unsignedBigInteger('gestor_id')->index('avaliacao_noventa_feedbacks_gestor_id_foreign')->comment('usuário em sessãos');
            $table->integer('nota');
            $table->integer('quantidade_avaliacao');
            $table->string('gestor_imediato');
            $table->longText('observacao')->nullable();
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
        Schema::dropIfExists('avaliacao_noventa_feedbacks');
    }
}
