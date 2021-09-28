<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToEtapaTiposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('etapa_tipos', function (Blueprint $table) {
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
        Schema::table('etapa_tipos', function (Blueprint $table) {
            $table->dropForeign('etapa_tipos_cliente_id_foreign');
        });
    }
}
