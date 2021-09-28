<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePesquisaClimaPerguntasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pesquisa_clima_perguntas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tipo_id')->index('pesquisa_clima_perguntas_tipo_id_foreign');
            $table->longText('pergunta');
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
        Schema::dropIfExists('pesquisa_clima_perguntas');
    }
}
