<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurriculosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('curriculos', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('cpf')->unique();
            $table->string('rg')->nullable();
            $table->string('orgao_expeditor')->nullable();
            $table->string('carteira_trabalho')->nullable();
            $table->string('nome');
            $table->string('cnh')->nullable();
            $table->date('nascimento');
            $table->string('logradouro', 255)->nullable();
            $table->string('complemento', 255)->nullable();
            $table->string('bairro', 255)->nullable();
            $table->string('municipio', 255)->nullable();
            $table->string('uf', 2)->nullable();
            $table->string('cep')->nullable();
            $table->string('email')->nullable();
            $table->unsignedBigInteger('formacao')->nullable()->index('curriculos_formacao_foreign');
            $table->string('formacao_instituicao')->nullable();
            $table->string('formacao_curso')->nullable();
            $table->string('formacao_status')->nullable()->default('Concluido');
            $table->unsignedBigInteger('vaga_pretendida')->nullable()->index('curriculos_vaga_pretendida_foreign');
            $table->string('uf_vaga')->nullable()->default('MA');
            $table->unsignedBigInteger('municipio_id')->nullable()->index('curriculos_municipio_id_foreign');
            $table->boolean('pcd')->nullable();
            $table->string('cid')->nullable();
            $table->boolean('viajar')->nullable();
            $table->boolean('lido')->nullable()->default(0);
            $table->unsignedBigInteger('usuario_lido')->nullable()->index('curriculos_usuario_lido_foreign');
            $table->timestamps();
            $table->dateTime('datalido')->nullable();
            $table->string('filiacao_pai')->nullable();
            $table->string('filiacao_mae')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('curriculos');
    }
}
