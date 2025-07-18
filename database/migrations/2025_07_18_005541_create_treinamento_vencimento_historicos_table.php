<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTreinamentoVencimentoHistoricosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('treinamento_vencimento_historicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedInteger('treinamento_id');
            $table->unsignedBigInteger('user_id');
            $table->json('treinamentos_vencimentos');
            $table->timestamps();

            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos');
            $table->foreign('empresa_id')->references('id')->on('clientes');
            $table->foreign('treinamento_id')->references('id')->on('treinamentos');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('treinamento_vencimento_historicos');
    }
}
