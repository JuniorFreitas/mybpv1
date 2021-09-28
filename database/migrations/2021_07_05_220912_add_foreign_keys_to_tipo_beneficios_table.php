<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToTipoBeneficiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tipo_beneficios', function (Blueprint $table) {
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
        Schema::table('tipo_beneficios', function (Blueprint $table) {
            $table->dropForeign('tipo_beneficios_cliente_id_foreign');
        });
    }
}
