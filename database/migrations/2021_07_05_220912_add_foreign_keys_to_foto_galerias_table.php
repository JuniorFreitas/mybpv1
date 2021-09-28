<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToFotoGaleriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('foto_galerias', function (Blueprint $table) {
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('galeria_id')->references('id')->on('galerias')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('foto_galerias', function (Blueprint $table) {
            $table->dropForeign('foto_galerias_arquivo_id_foreign');
            $table->dropForeign('foto_galerias_galeria_id_foreign');
        });
    }
}
