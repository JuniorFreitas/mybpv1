<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNpsRespostaItensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nps_resposta_itens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('nps_resposta_id');
            $table->unsignedBigInteger('nps_pergunta_id');
            $table->unsignedTinyInteger('nota'); // 1 a 5
            $table->timestamps();

            $table->foreign('nps_resposta_id')->references('id')->on('nps_respostas')->cascadeOnDelete();
            $table->foreign('nps_pergunta_id')->references('id')->on('nps_perguntas')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nps_resposta_itens');
    }
}
