<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPesquisaClimaPerguntaRespostasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pesquisa_clima_pergunta_respostas', function (Blueprint $table) {
            $table->foreign('pergunta_id')->references('id')->on('pesquisa_clima_perguntas')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pesquisa_clima_pergunta_respostas', function (Blueprint $table) {
            $table->dropForeign('pesquisa_clima_pergunta_respostas_pergunta_id_foreign');
        });
    }
}
