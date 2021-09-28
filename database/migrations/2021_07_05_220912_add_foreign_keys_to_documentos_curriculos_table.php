<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToDocumentosCurriculosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('documentos_curriculos', function (Blueprint $table) {
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('curriculo_id')->references('id')->on('curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('documentos_curriculos', function (Blueprint $table) {
            $table->dropForeign('documentos_curriculos_arquivo_id_foreign');
            $table->dropForeign('documentos_curriculos_curriculo_id_foreign');
        });
    }
}
