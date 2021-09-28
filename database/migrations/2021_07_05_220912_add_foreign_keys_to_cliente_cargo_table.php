<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToClienteCargoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cliente_cargo', function (Blueprint $table) {
            $table->foreign('cargo_id')->references('id')->on('vagas')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cliente_cargo', function (Blueprint $table) {
            $table->dropForeign('cliente_cargo_cargo_id_foreign');
            $table->dropForeign('cliente_cargo_cliente_id_foreign');
        });
    }
}
