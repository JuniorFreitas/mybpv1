<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferenciaPrevistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transferencia_previstas', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('colaborador_id')->nullable();
            $table->unsignedBigInteger('centro_custo_origem_id');
            $table->unsignedBigInteger('centro_custo_destino_id');
            $table->date('data_transferencia');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('solicitante')->nullable();
            $table->text('obs')->nullable();

            $table->unsignedBigInteger('user_aprovacao_id')->nullable();
            $table->dateTime('data_aprovacao')->nullable();
            $table->text('obs_aprovacao')->nullable();
            $table->string('status_aprovacao')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->timestamps();

            $table->foreign('colaborador_id')->references('id')->on('curriculos')->cascadeOnDelete();
            $table->foreign('centro_custo_origem_id')->references('id')->on('centro_custos')->cascadeOnDelete();
            $table->foreign('centro_custo_destino_id')->references('id')->on('centro_custos')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('user_aprovacao_id')->references('id')->on('users');
            $table->foreign('empresa_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transferencia_previstas');
    }
}
