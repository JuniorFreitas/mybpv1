<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvaliacoesRespostasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avaliacoes_respostas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedBigInteger('avaliacao_feedback_id')->nullable();
            $table->foreign('avaliacao_feedback_id')->references('id')->on('avaliacoes_feedbacks')->cascadeOnDelete();
            $table->unsignedBigInteger('topico_id')->nullable();
            $table->foreign('topico_id')->references('id')->on('avaliacoes_topicos')->onDelete('CASCADE');
            $table->unsignedInteger('nota');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avaliacoes_respostas');
    }
}
