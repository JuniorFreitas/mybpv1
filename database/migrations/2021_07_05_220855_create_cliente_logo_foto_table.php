<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteLogoFotoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cliente_logo_foto', function (Blueprint $table) {
            $table->unsignedBigInteger('arquivo_id')->index('cliente_logo_foto_arquivo_id_foreign');
            $table->unsignedBigInteger('cliente_id')->index('cliente_logo_foto_cliente_id_foreign');
            $table->unsignedBigInteger('ordem');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cliente_logo_foto');
    }
}
