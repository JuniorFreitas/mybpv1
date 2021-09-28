<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFornecedorServicoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fornecedor_servico', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fornecedor_id')->index('fornecedor_servico_fornecedor_id_foreign');
            $table->unsignedBigInteger('tipo_servico_fornecedor_id')->nullable()->index('fornecedor_servico_tipo_servico_fornecedor_id_foreign');
            $table->string('vencimento')->nullable()->comment('quando for utilizado para fornecedor');
            $table->date('data_inicio')->nullable();
            $table->date('data_encerramento')->nullable();
            $table->longText('escopo')->nullable();
            $table->string('valor')->nullable();
            $table->string('tipo_faturamento')->nullable();
            $table->string('status')->nullable();
            $table->string('feedback')->nullable();
            $table->boolean('ativo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fornecedor_servico');
    }
}
