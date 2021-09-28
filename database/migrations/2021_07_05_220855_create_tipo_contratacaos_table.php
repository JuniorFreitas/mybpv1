<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTipoContratacaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tipo_contratacaos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('requisicao_vaga_id')->index('tipo_contratacaos_requisicao_vaga_id_foreign');
            $table->string('posicao');
            $table->string('processo');
            $table->string('contrato');
            $table->string('local_trabalho');
            $table->string('horario');
            $table->unsignedBigInteger('gestor_id')->nullable()->index('tipo_contratacaos_gestor_id_foreign');
            $table->string('gestor')->nullable();
            $table->string('ppra')->nullable();
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
        Schema::dropIfExists('tipo_contratacaos');
    }
}
