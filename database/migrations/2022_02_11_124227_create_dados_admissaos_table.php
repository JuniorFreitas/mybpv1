<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDadosAdmissaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dados_admissaos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('admissao_id')->nullable();
            $table->foreign('admissao_id')->references('id')->on('admissoes');
            $table->string('ctps_numero')->nullable();
            $table->string('ctps_serie')->nullable();
            $table->date('ctps_data_emissao')->nullable();
            $table->string('titulo_eleitor_numero')->nullable();
            $table->string('titulo_eleitor_sessao')->nullable();
            $table->string('titulo_eleitor_zona')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dados_admissaos');
    }
}
