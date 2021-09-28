<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToOcorrenciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ocorrencias', function (Blueprint $table) {
            $table->foreign('cliente_id')->references('id')->on('clientes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('quem_atualizou')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('quem_criou')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('quem_finalizou')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('setor_id')->references('id')->on('ocorrencias_setores')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('usuario_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ocorrencias', function (Blueprint $table) {
            $table->dropForeign('ocorrencias_cliente_id_foreign');
            $table->dropForeign('ocorrencias_quem_atualizou_foreign');
            $table->dropForeign('ocorrencias_quem_criou_foreign');
            $table->dropForeign('ocorrencias_quem_finalizou_foreign');
            $table->dropForeign('ocorrencias_setor_id_foreign');
            $table->dropForeign('ocorrencias_usuario_id_foreign');
        });
    }
}
