<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPesquisaClimaPerguntasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pesquisa_clima_perguntas', function (Blueprint $table) {
            $table->foreign('tipo_id')->references('id')->on('pesquisa_clima_tipos')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pesquisa_clima_perguntas', function (Blueprint $table) {
            $table->dropForeign('pesquisa_clima_perguntas_tipo_id_foreign');
        });
    }
}
