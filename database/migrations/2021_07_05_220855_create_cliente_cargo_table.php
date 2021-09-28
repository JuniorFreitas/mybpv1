<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClienteCargoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cliente_cargo', function (Blueprint $table) {
            $table->unsignedBigInteger('cliente_id')->index('cliente_cargo_cliente_id_foreign');
            $table->unsignedBigInteger('cargo_id')->index('cliente_cargo_cargo_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cliente_cargo');
    }
}
