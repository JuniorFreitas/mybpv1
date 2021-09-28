<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFornecedoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fornecedores', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('tipo')->comment('Fornecedor, Terceiro, Parceiro');
            $table->string('cnpj')->nullable();
            $table->string('cpf')->nullable();
            $table->string('nome')->nullable();
            $table->string('tipo_pessoa');
            $table->string('razao_social')->nullable();
            $table->string('nome_fantasia')->nullable();
            $table->string('cep')->nullable();
            $table->string('logradouro', 255)->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento', 255)->nullable();
            $table->string('bairro', 255)->nullable();
            $table->string('municipio', 255)->nullable();
            $table->string('uf', 255)->nullable();
            $table->string('contato')->nullable();
            $table->string('email')->nullable();
            $table->date('aniversario')->nullable();
            $table->boolean('ativo');
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
        Schema::dropIfExists('fornecedores');
    }
}
