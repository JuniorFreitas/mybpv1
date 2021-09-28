<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToTreinamentoEventosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('treinamento_eventos', function (Blueprint $table) {
            $table->foreign('cliente_id')->references('id')->on('clientes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('empresa_treinamento_id')->references('id')->on('empresa_treinamentos')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('treinamento_sgi_id')->references('id')->on('treinamento_sgi')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('treinamento_eventos', function (Blueprint $table) {
            $table->dropForeign('treinamento_eventos_cliente_id_foreign');
            $table->dropForeign('treinamento_eventos_empresa_treinamento_id_foreign');
            $table->dropForeign('treinamento_eventos_treinamento_sgi_id_foreign');
        });
    }
}
