<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ferias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedInteger('admissao_id');
            $table->unsignedBigInteger('periodo_aquisitivo_id');
            $table->date('data_saida');
            $table->date('data_retorno');
            $table->date('ultima_data');
            $table->unsignedInteger('qnt_dias')->nullable();
            $table->unsignedInteger('dias_saldo')->nullable();
            $table->boolean('tem_faltas');
            $table->unsignedInteger('qnt_faltas')->nullable();
            $table->unsignedBigInteger('solicitante_id');
            $table->text('obs_solicitante')->nullable();
            $table->dateTime('data_solicitacao');
            $table->unsignedBigInteger('gestor_id')->nullable();
            $table->unsignedBigInteger('gestor_aprovacao_id')->nullable();
            $table->text('obs_gestor')->nullable();
            $table->string('status_aprovacao_gestor')->nullable();
            $table->dateTime('data_aprovacao_gestor')->nullable();
            $table->unsignedBigInteger('rh_aprovacao_id')->nullable();
            $table->text('obs_rh')->nullable();
            $table->string('status_aprovacao_rh')->nullable();
            $table->dateTime('data_aprovacao_rh')->nullable();
            $table->string('status_ferias')->nullable();
            $table->dateTime('data_status_ferias')->nullable();
            $table->unsignedBigInteger('ferias_prevista_id')->nullable();
            $table->boolean('aprovado_via_script')->default(false);
            $table->timestamps();


            $table->foreign('empresa_id')->references('id')->on('users');
            $table->foreign('admissao_id')->references('id')->on('admissoes');
            $table->foreign('periodo_aquisitivo_id')->references('id')->on('periodos_aquisitivos');
            $table->foreign('solicitante_id')->references('id')->on('users');
            $table->foreign('gestor_aprovacao_id')->references('id')->on('users');
            $table->foreign('gestor_id')->references('id')->on('users');
            $table->foreign('rh_aprovacao_id')->references('id')->on('users');
            $table->foreign('ferias_prevista_id')->references('id')->on('ferias_previstas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ferias');
    }
}
