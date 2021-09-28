<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPesquisaClimaClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pesquisa_clima_clientes', function (Blueprint $table) {
            $table->foreign('cliente_id')->references('id')->on('clientes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('tipo_id')->references('id')->on('pesquisa_clima_tipos')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pesquisa_clima_clientes', function (Blueprint $table) {
            $table->dropForeign('pesquisa_clima_clientes_cliente_id_foreign');
            $table->dropForeign('pesquisa_clima_clientes_tipo_id_foreign');
        });
    }
}
