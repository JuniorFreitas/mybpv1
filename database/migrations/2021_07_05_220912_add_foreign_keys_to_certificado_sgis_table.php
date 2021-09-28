<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToCertificadoSgisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('certificado_sgis', function (Blueprint $table) {
            $table->foreign('cliente_id')->references('id')->on('clientes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('pessoa_evento_id')->references('id')->on('treinamento_eventos')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('treinamento_evento_id')->references('id')->on('treinamento_eventos')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('certificado_sgis', function (Blueprint $table) {
            $table->dropForeign('certificado_sgis_cliente_id_foreign');
            $table->dropForeign('certificado_sgis_pessoa_evento_id_foreign');
            $table->dropForeign('certificado_sgis_treinamento_evento_id_foreign');
        });
    }
}
