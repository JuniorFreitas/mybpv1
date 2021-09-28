<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFornecedorServicoAnexosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fornecedor_servico_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('fornecedor_servico_id')->index('fornecedor_servico_anexos_fornecedor_servico_id_foreign');
            $table->unsignedBigInteger('arquivo_id')->index('fornecedor_servico_anexos_arquivo_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fornecedor_servico_anexos');
    }
}
