<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVagaProjetosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vaga_projetos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('projeto_id');
            $table->unsignedBigInteger('vaga_aberta_id');
            $table->integer('qnt_total');
            $table->integer('qnt_preenchida');

            $table->foreign('projeto_id')->references('id')->on('projetos')->cascadeOnDelete();
            $table->foreign('vaga_aberta_id')->references('id')->on('vagas_abertas')->cascadeOnDelete();
            $table->foreign('empresa_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vaga_projetos');
    }
}
