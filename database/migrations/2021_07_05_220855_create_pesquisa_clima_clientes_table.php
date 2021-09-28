<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePesquisaClimaClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pesquisa_clima_clientes', function (Blueprint $table) {
            $table->unsignedBigInteger('tipo_id')->index('pesquisa_clima_clientes_tipo_id_foreign');
            $table->unsignedBigInteger('cliente_id')->index('pesquisa_clima_clientes_cliente_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pesquisa_clima_clientes');
    }
}
