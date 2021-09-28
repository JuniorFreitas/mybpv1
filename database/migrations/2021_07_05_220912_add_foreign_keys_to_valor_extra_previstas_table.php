<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToValorExtraPrevistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('valor_extra_previstas', function (Blueprint $table) {
            $table->foreign('centro_custo_id')->references('id')->on('centro_custos')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('colaborador_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('valor_extra_previstas', function (Blueprint $table) {
            $table->dropForeign('valor_extra_previstas_centro_custo_id_foreign');
            $table->dropForeign('valor_extra_previstas_cliente_id_foreign');
            $table->dropForeign('valor_extra_previstas_colaborador_id_foreign');
            $table->dropForeign('valor_extra_previstas_user_id_foreign');
        });
    }
}
