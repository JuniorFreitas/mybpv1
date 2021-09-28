<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPeriodoJornadasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('periodo_jornadas', function (Blueprint $table) {
            $table->foreign('jornada_id')->references('id')->on('escala_jornadas')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('periodo_jornadas', function (Blueprint $table) {
            $table->dropForeign('periodo_jornadas_jornada_id_foreign');
        });
    }
}
