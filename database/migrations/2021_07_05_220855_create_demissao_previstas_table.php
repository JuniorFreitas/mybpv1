<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDemissaoPrevistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demissao_previstas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cliente_id')->index('demissao_previstas_cliente_id_foreign');
            $table->unsignedBigInteger('colaborador_id')->index('demissao_previstas_colaborador_id_foreign');
            $table->unsignedBigInteger('centro_custo_id')->index('demissao_previstas_centro_custo_id_foreign');
            $table->string('aviso', 255)->nullable();
            $table->date('data_demissao');
            $table->date('data_pagamento');
            $table->decimal('valor', 11);
            $table->unsignedBigInteger('user_id')->nullable()->index('demissao_previstas_user_id_foreign');
            $table->string('solicitante')->nullable();
            $table->string('status')->nullable();
            $table->text('obs')->nullable();
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
        Schema::dropIfExists('demissao_previstas');
    }
}
