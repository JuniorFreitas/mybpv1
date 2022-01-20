<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeriasPrevistaDadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ferias_prevista_dados', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ferias_prevista_id')->comment('referencia ao colaborador HASONE');
            $table->foreign('ferias_prevista_id')->references('id')->on('ferias_prevista_movs');
            $table->unsignedBigInteger('centro_custo_id')->nullable();
            $table->foreign('centro_custo_id')->references('id')->on('centro_custos');
            $table->unsignedBigInteger('solicitante_id')->nullable();
            $table->foreign('solicitante_id')->references('id')->on('users');
            $table->date('data_saida')->nullable();
            $table->integer('qnt_dias')->nullable();
            $table->date('data_retorno')->nullable();
            $table->integer('dias_saldo')->nullable();
            $table->string('status')->nullable();
            $table->text('obs')->nullable();
            $table->string('periodo_aquisitivo')->nullable();
            $table->date('ultima_data')->nullable();

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
        Schema::dropIfExists('ferias_prevista_dados');
    }
}
