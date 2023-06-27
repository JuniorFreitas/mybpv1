<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMudancaCargoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mudanca_cargo', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedInteger('admissao_id');
            $table->unsignedBigInteger('colaborador_id');
            $table->boolean('mantem_centro_custo')->default(true);
            $table->unsignedBigInteger('anterior_centro_custo_id')->nullable();
            $table->unsignedBigInteger('anterior_centro_custo_filial_id')->nullable();
            $table->unsignedBigInteger('novo_centro_custo_id')->nullable();
            $table->unsignedBigInteger('novo_centro_custo_filial_id')->nullable();
            $table->boolean('mantem_cargo')->default(false);
            $table->unsignedBigInteger('anterior_vaga_aberta_id');
            $table->unsignedBigInteger('nova_vaga_aberta_id');
            $table->boolean('mantem_funcao')->nullable(true);
            $table->string('anterior_funcao')->nullable();
            $table->string('nova_funcao')->nullable();
            $table->boolean('mantem_salario')->default(false);
            $table->decimal('anterior_salario', 11,2);
            $table->decimal('novo_salario', 11,2);
            $table->unsignedBigInteger('solicitante_id');
            $table->text('obs_solicitante')->nullable();
            $table->dateTime('data_solicitacao');
            $table->unsignedBigInteger('gestor_id')->nullable();
            $table->unsignedBigInteger('gestor_aprovacao_id')->nullable();
            $table->text('obs_gestor_aprovacao')->nullable();
            $table->string('status_aprovacao_gestor')->nullable();
            $table->dateTime('data_aprovacao_gestor')->nullable();
            $table->unsignedBigInteger('rh_aprovacao_id')->nullable();
            $table->text('obs_rh')->nullable();
            $table->string('status_aprovacao_rh')->nullable();
            $table->dateTime('data_aprovacao_rh')->nullable();
            $table->boolean('aprovado_via_script')->default(false);
            $table->timestamps();
            $table->unsignedBigInteger('quem_deletou_id')->nullable();
            $table->softDeletes();

            $table->foreign('empresa_id')->references('id')->on('clientes');
            $table->foreign('admissao_id')->references('id')->on('admissoes');
            $table->foreign('colaborador_id')->references('id')->on('users');
            $table->foreign('anterior_centro_custo_id')->references('id')->on('centro_custos');
            $table->foreign('anterior_centro_custo_filial_id')->references('id')->on('centro_custo_filials');
            $table->foreign('novo_centro_custo_id')->references('id')->on('centro_custos');
            $table->foreign('novo_centro_custo_filial_id')->references('id')->on('centro_custo_filials');
            $table->foreign('anterior_vaga_aberta_id')->references('id')->on('vagas_abertas');
            $table->foreign('nova_vaga_aberta_id')->references('id')->on('vagas_abertas');
            $table->foreign('solicitante_id')->references('id')->on('users');
            $table->foreign('gestor_aprovacao_id')->references('id')->on('users');
            $table->foreign('gestor_id')->references('id')->on('users');
            $table->foreign('rh_aprovacao_id')->references('id')->on('users');
            $table->foreign('quem_deletou_id')->references('id')->on('users');
        });

        Schema::create('mudanca_cargo_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('mudanca_cargo_id');
            $table->unsignedInteger('arquivo_id');
            $table->foreign('mudanca_cargo_id')->references('id')->on('mudanca_cargo')->onDelete('CASCADE');
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mudanca_cargo_anexos');
        Schema::dropIfExists('mudanca_cargo');
    }
}
