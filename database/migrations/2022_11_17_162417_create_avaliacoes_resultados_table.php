<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvaliacoesResultadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avaliacoes_resultados', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('avaliacao_feedback_id')->nullable();
            $table->foreign('avaliacao_feedback_id')->references('id')->on('avaliacoes_feedbacks')->cascadeOnDelete();
            $table->unsignedBigInteger('gestor_id')->nullable();
            $table->foreign('gestor_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->unsignedBigInteger('topico_id')->nullable();
            $table->foreign('topico_id')->references('id')->on('avaliacoes_topicos')->onDelete('CASCADE');
            $table->unsignedInteger('nota');
            $table->text('plano_de_acao');
            $table->string('responsavel');
            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avaliacoes_resultados');
    }
}
