<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNpsCiclosTable extends Migration
{
    /**
     * Run the migrations.
     * Ciclos/campanhas NPS: período com nome para consolidar respostas.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('nps_ciclos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 120)->comment('Ex: Ciclo Q1 2026, Campanha Pós-Implementação');
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['data_inicio', 'data_fim']);
            $table->index('ativo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('nps_ciclos');
    }
}
