<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSimuladoRespostasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('simulado_respostas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulado_pergunta_id')->index('simulado_respostas_simulado_pergunta_id_foreign');
            $table->longText('resposta');
            $table->boolean('correto');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('simulado_respostas');
    }
}
