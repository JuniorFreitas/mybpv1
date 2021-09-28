<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeriasPrevistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ferias_previstas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('cliente_id')->index('ferias_previstas_cliente_id_foreign');
            $table->unsignedBigInteger('colaborador_id')->index('ferias_previstas_colaborador_id_foreign');
            $table->unsignedBigInteger('centro_custo_id')->index('ferias_previstas_centro_custo_id_foreign');
            $table->date('data_saida');
            $table->integer('qnt_dias');
            $table->date('data_retorno');
            $table->integer('dias_saldo');
            $table->unsignedBigInteger('user_id')->nullable()->index('ferias_previstas_user_id_foreign');
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
        Schema::dropIfExists('ferias_previstas');
    }
}
