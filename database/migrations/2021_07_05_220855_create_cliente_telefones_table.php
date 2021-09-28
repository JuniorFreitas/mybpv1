<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteTelefonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cliente_telefones', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 20);
            $table->string('pais', 4)->default('55');
            $table->string('numero', 16);
            $table->string('ramal', 5)->nullable();
            $table->string('detalhe')->nullable();
            $table->unsignedBigInteger('cliente_id')->index('cliente_telefones_cliente_id_foreign');
            $table->boolean('principal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cliente_telefones');
    }
}
