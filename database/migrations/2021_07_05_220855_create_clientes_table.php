<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->string('tipo_cliente')->default('Cliente');
            $table->string('cnpj')->nullable();
            $table->string('cpf')->nullable();
            $table->string('nome')->nullable();
            $table->string('apelido')->nullable();
            $table->string('tipo');
            $table->string('razao_social')->nullable();
            $table->string('nome_fantasia')->nullable();
            $table->unsignedBigInteger('area_id')->index('clientes_area_id_foreign');
            $table->string('ramo')->nullable();
            $table->string('cep')->nullable();
            $table->string('logradouro', 255)->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento', 255)->nullable();
            $table->string('bairro', 255)->nullable();
            $table->string('municipio', 255)->nullable();
            $table->string('uf', 255)->nullable();
            $table->string('contato')->nullable();
            $table->string('email')->nullable();
            $table->string('tel_principal')->nullable();
            $table->date('aniversario')->nullable();
            $table->string('como_conheceu')->nullable();
            $table->string('como_conheceu_outro')->nullable();
            $table->text('politica_ehs')->nullable();
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
        Schema::dropIfExists('clientes');
    }
}
