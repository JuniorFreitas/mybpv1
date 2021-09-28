<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePeriodoPontoEletronicosTable extends Migration
{
    public function up()
    {
        Schema::create('periodo_ponto_eletronicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ponto_id');

            $table->string('autenticacao_entrada',40)->nullable();
            $table->dateTime('entrada');
            $table->boolean('facial_entrada');
            $table->unsignedInteger('arquivo_id_entrada')->nullable(); //foto da entrada
            $table->double('lat_entrada')->nullable();
            $table->double('long_entrada')->nullable();

            $table->string('autenticacao_saida',40)->nullable();
            $table->dateTime('saida')->nullable();
            $table->boolean('facial_saida')->nullable();
            $table->unsignedInteger('arquivo_id_saida')->nullable(); //foto da saida
            $table->double('lat_saida')->nullable();
            $table->double('long_saida')->nullable();

            $table->integer('minutos'); // em minutos

            $table->foreign('ponto_id')->references('id')->on('ponto_eletronicos')->onDelete('cascade');
            $table->foreign('arquivo_id_entrada')->references('id')->on('arquivos')->onDelete('set null');
            $table->foreign('arquivo_id_saida')->references('id')->on('arquivos')->onDelete('set null');

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
        Schema::dropIfExists('periodo_ponto_eletronicos');
    }
}
