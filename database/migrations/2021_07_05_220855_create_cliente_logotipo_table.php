<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteLogotipoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cliente_logotipo', function (Blueprint $table) {
            $table->unsignedBigInteger('cliente_id')->index('cliente_logotipo_cliente_id_foreign');
            $table->unsignedBigInteger('arquivo_id')->index('cliente_logotipo_arquivo_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cliente_logotipo');
    }
}
