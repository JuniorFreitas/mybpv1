<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmpresaFornecedoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empresa_fornecedores', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->index('empresa_fornecedores_empresa_id_foreign');
            $table->unsignedBigInteger('fornecedor_id')->index('empresa_fornecedores_fornecedor_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('empresa_fornecedores');
    }
}
