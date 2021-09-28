<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvaliacaoAnualFeedbackQuantidadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avaliacao_anual_feedback_quantidades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->index('avaliacao_anual_feedback_quantidades_feedback_id_foreign');
            $table->integer('quantidade_avaliacao');
            $table->unsignedBigInteger('gestor_id')->index('avaliacao_anual_feedback_quantidades_gestor_id_foreign');
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
        Schema::dropIfExists('avaliacao_anual_feedback_quantidades');
    }
}
