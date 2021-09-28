<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToServicosProspectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('servicos_prospects', function (Blueprint $table) {
            $table->foreign('cliente_id')->references('id')->on('clientes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('servico_id')->references('id')->on('servicos')->onUpdate('RESTRICT')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('servicos_prospects', function (Blueprint $table) {
            $table->dropForeign('servicos_prospects_cliente_id_foreign');
            $table->dropForeign('servicos_prospects_servico_id_foreign');
        });
    }
}
