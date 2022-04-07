<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvaliacaoNoventaVencimentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avaliacao_noventa_vencimentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');;
            $table->date('prazo_dez_inicial')->nullable();
            $table->date('prazo_cinco_inicial')->nullable();
            $table->date('prazo_dia_inicial')->nullable();
            $table->date('prazo_dez_final')->nullable();
            $table->date('prazo_cinco_final')->nullable();
            $table->date('prazo_dia_final')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avaliacao_noventa_vencimentos');
    }
}
