<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePesquisaClimaPerguntaRespostaCandidatosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pesquisa_clima_pergunta_resposta_candidatos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->index('pesquisa_clima_pergunta_resposta_candidatos_feedback_id_foreign');
            $table->unsignedBigInteger('pergunta_id')->index('pesquisa_clima_pergunta_resposta_candidatos_pergunta_id_foreign');
            $table->unsignedBigInteger('resposta_id')->nullable()->index('pesquisa_clima_pergunta_resposta_candidatos_resposta_id_foreign');
            $table->string('respostadigitada')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('cliente_id')->nullable()->index('pesquisa_clima_pergunta_resposta_candidatos_cliente_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pesquisa_clima_pergunta_resposta_candidatos');
    }
}
