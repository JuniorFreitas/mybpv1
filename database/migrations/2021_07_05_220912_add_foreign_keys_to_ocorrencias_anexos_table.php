<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToOcorrenciasAnexosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ocorrencias_anexos', function (Blueprint $table) {
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('resposta_id')->references('id')->on('ocorrencias_respostas')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ocorrencias_anexos', function (Blueprint $table) {
            $table->dropForeign('ocorrencias_anexos_arquivo_id_foreign');
            $table->dropForeign('ocorrencias_anexos_resposta_id_foreign');
        });
    }
}
