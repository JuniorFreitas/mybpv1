<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSimuladoPerguntasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('simulado_perguntas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulado_id')->index('simulado_perguntas_simulado_id_foreign');
            $table->longText('enunciado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('simulado_perguntas');
    }
}
