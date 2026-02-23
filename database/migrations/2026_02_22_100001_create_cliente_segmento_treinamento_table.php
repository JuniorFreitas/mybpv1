<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteSegmentoTreinamentoTable extends Migration
{
    /**
     * Run the migrations.
     * Pivot: empresa (cliente) pode ter vários segmentos de treinamento habilitados.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cliente_segmento_treinamento', function (Blueprint $table) {
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('segmento_treinamento_id');
            $table->primary(['cliente_id', 'segmento_treinamento_id'], 'cliente_seg_treinamento_primary');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('segmento_treinamento_id')->references('id')->on('segmentos_treinamento')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cliente_segmento_treinamento');
    }
}
