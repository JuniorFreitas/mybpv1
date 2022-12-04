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
            $table->unsignedBigInteger('empresa_id');
            $table->string('origem_feedback');
            $table->boolean('principal')->default(false);
            $table->unsignedBigInteger('avaliador_id')->nullable();
            $table->unsignedBigInteger('funcionario_id')->nullable();
            $table->unsignedInteger('nota_final_total')->nullable();
            $table->dateTime('inicio_feedback')->nullable();
            $table->dateTime('fim_feedback')->nullable();
            $table->text('comentario')->nullable();
            $table->string('status');

            $table->foreign('avaliacao_id')->references('id')->on('avaliacoes')->onDelete('CASCADE');
            $table->foreign('empresa_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('avaliador_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->foreign('funcionario_id')->references('id')->on('users')->onDelete('CASCADE');
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
