<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSimuladoCandidatosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('simulado_candidatos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('simulado_vaga_id')->index('simulado_candidatos_simulado_vaga_id_foreign');
            $table->unsignedBigInteger('feedback_id')->nullable()->index('simulado_candidatos_feedback_id_foreign');
            $table->integer('duracao_segundos');
            $table->boolean('finalizado');
            $table->dateTime('data_finalizacao')->nullable();
            $table->integer('acertos')->nullable()->default(0);
            $table->timestamps();
            $table->string('status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('simulado_candidatos');
    }
}
