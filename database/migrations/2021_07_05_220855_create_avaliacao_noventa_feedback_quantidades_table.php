<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvaliacaoNoventaFeedbackQuantidadesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avaliacao_noventa_feedback_quantidades', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->index('avaliacao_noventa_feedback_quantidades_feedback_id_foreign');
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
        Schema::dropIfExists('avaliacao_noventa_feedback_quantidades');
    }
}
