<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToParabensEnviadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parabens_enviados', function (Blueprint $table) {
            $table->foreign('cliente_id')->references('id')->on('clientes')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('curriculo_id')->references('id')->on('curriculos')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parabens_enviados', function (Blueprint $table) {
            $table->dropForeign('parabens_enviados_cliente_id_foreign');
            $table->dropForeign('parabens_enviados_curriculo_id_foreign');
        });
    }
}
