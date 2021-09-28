<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToCurriculoAtualizacaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('curriculo_atualizacaos', function (Blueprint $table) {
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
        Schema::table('curriculo_atualizacaos', function (Blueprint $table) {
            $table->dropForeign('curriculo_atualizacaos_curriculo_id_foreign');
        });
    }
}
