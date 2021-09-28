<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEscalaJornadasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('escala_jornadas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('escala_id')->index('escala_jornadas_escala_id_foreign');
            $table->string('tipo')->default('dia_trabalhado');
            $table->integer('repetir');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('escala_jornadas');
    }
}
