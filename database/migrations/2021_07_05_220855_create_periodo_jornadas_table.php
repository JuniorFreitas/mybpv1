<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeriodoJornadasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('periodo_jornadas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('jornada_id')->index('periodo_jornadas_jornada_id_foreign');
            $table->time('entrada');
            $table->time('saida');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('periodo_jornadas');
    }
}
