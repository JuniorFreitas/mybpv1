<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvaliacoesFeedbacksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avaliacoes_feedbacks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('avaliacao_id')->nullable();
            $table->foreign('avaliacao_id')->references('id')->on('avaliacoes')->onDelete('CASCADE');
            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('users')->cascadeOnDelete();
            $table->string('origem_feedback');
            $table->unsignedBigInteger('feedback_id')->nullable();
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onDelete('CASCADE');
            $table->unsignedBigInteger('avaliador_id')->nullable();
            $table->foreign('avaliador_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->unsignedInteger('nota_final_total')->nullable();
            $table->dateTime('inicio_feedback')->nullable();
            $table->dateTime('fim_feedback')->nullable();
            $table->text('comentario')->nullable();
            $table->string('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avaliacoes_feedbacks');
    }
}
