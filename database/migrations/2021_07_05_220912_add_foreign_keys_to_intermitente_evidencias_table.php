<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToIntermitenteEvidenciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('intermitente_evidencias', function (Blueprint $table) {
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('intermitente_id')->references('id')->on('intermitentes')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('intermitente_evidencias', function (Blueprint $table) {
            $table->dropForeign('intermitente_evidencias_arquivo_id_foreign');
            $table->dropForeign('intermitente_evidencias_intermitente_id_foreign');
        });
    }
}
