<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTreinamentoSgiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('treinamento_sgi', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('titulo_certificado');
            $table->longText('conteudo_abordado')->nullable();
            $table->longText('conteudo_programatico')->nullable();
            $table->integer('carga_horaria');
            $table->integer('validade')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('treinamento_sgi');
    }
}
