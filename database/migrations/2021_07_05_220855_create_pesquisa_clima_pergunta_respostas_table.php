<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePesquisaClimaPerguntaRespostasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pesquisa_clima_pergunta_respostas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pergunta_id')->index('pesquisa_clima_pergunta_respostas_pergunta_id_foreign');
            $table->longText('resposta');
            $table->boolean('ativo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pesquisa_clima_pergunta_respostas');
    }
}
