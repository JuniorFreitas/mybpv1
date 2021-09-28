<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToTreinamentoEventoAnexosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('treinamento_evento_anexos', function (Blueprint $table) {
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onUpdate('RESTRICT')->onDelete('CASCADE');
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
        Schema::table('treinamento_evento_anexos', function (Blueprint $table) {
            $table->dropForeign('treinamento_evento_anexos_arquivo_id_foreign');
            $table->dropForeign('treinamento_evento_anexos_treinamento_evento_id_foreign');
        });
    }
}
