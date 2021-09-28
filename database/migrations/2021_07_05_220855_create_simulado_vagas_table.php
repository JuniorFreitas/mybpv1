<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSimuladoVagasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('simulado_vagas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulado_id')->index('simulado_vagas_simulado_id_foreign');
            $table->unsignedBigInteger('vaga_id')->index('simulado_vagas_vaga_id_foreign');
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim');
            $table->integer('duracao');
            $table->boolean('online')->nullable()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('simulado_vagas');
    }
}
