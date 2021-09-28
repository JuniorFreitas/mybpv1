<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToClienteAreaEtiquetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cliente_area_etiquetas', function (Blueprint $table) {
            $table->foreign('area_etiqueta_id')->references('id')->on('area_etiquetas')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cliente_area_etiquetas', function (Blueprint $table) {
            $table->dropForeign('cliente_area_etiquetas_area_etiqueta_id_foreign');
            $table->dropForeign('cliente_area_etiquetas_cliente_id_foreign');
        });
    }
}
