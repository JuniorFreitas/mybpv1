<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPesquisaClimaPerguntaRespostaCandidatosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pesquisa_clima_pergunta_resposta_candidatos', function (Blueprint $table) {
            $table->foreign('cliente_id')->references('id')->on('clientes')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('pergunta_id')->references('id')->on('pesquisa_clima_perguntas')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('resposta_id')->references('id')->on('pesquisa_clima_pergunta_respostas')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pesquisa_clima_pergunta_resposta_candidatos', function (Blueprint $table) {
            $table->dropForeign('pesquisa_clima_pergunta_resposta_candidatos_cliente_id_foreign');
            $table->dropForeign('pesquisa_clima_pergunta_resposta_candidatos_feedback_id_foreign');
            $table->dropForeign('pesquisa_clima_pergunta_resposta_candidatos_pergunta_id_foreign');
            $table->dropForeign('pesquisa_clima_pergunta_resposta_candidatos_resposta_id_foreign');
        });
    }
}
