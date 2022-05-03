<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVagaProjetoFeedbackTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vaga_projeto_feedback', function (Blueprint $table) {
            $table->unsignedBigInteger('feedback_id');
            $table->unsignedBigInteger('vaga_projeto_id');

            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->cascadeOnDelete();
            $table->foreign('vaga_projeto_id')->references('id')->on('vaga_projetos')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vaga_projeto_feedback');
    }
}
