<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteAreaEtiquetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cliente_area_etiquetas', function (Blueprint $table) {
            $table->unsignedBigInteger('cliente_id')->index('cliente_area_etiquetas_cliente_id_foreign');
            $table->unsignedBigInteger('area_etiqueta_id')->index('cliente_area_etiquetas_area_etiqueta_id_foreign');
            $table->string('numero_supervisor')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cliente_area_etiquetas');
    }
}
