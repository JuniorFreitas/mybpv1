<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToIntermitenteFixoPrevistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('intermitente_fixo_previstas', function (Blueprint $table) {
            $table->foreign('cargo_anterior_id')->references('id')->on('vagas')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('centro_custo_id')->references('id')->on('centro_custos')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('colaborador_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('novo_cargo_id')->references('id')->on('vagas')->onUpdate('RESTRICT')->onDelete('CASCADE');
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
        Schema::table('intermitente_fixo_previstas', function (Blueprint $table) {
            $table->dropForeign('intermitente_fixo_previstas_cargo_anterior_id_foreign');
            $table->dropForeign('intermitente_fixo_previstas_centro_custo_id_foreign');
            $table->dropForeign('intermitente_fixo_previstas_cliente_id_foreign');
            $table->dropForeign('intermitente_fixo_previstas_colaborador_id_foreign');
            $table->dropForeign('intermitente_fixo_previstas_novo_cargo_id_foreign');
            $table->dropForeign('intermitente_fixo_previstas_user_id_foreign');
        });
    }
}
