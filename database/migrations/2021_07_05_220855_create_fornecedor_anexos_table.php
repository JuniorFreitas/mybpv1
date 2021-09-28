<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFornecedorAnexosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fornecedor_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('fornecedor_id')->index('fornecedor_anexos_fornecedor_id_foreign');
            $table->unsignedBigInteger('arquivo_id')->index('fornecedor_anexos_arquivo_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fornecedor_anexos');
    }
}
