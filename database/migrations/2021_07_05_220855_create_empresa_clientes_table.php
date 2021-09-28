<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmpresaClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('empresa_clientes', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->index('empresa_clientes_empresa_id_foreign');
            $table->unsignedBigInteger('cliente_id')->index('empresa_clientes_cliente_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('empresa_clientes');
    }
}
