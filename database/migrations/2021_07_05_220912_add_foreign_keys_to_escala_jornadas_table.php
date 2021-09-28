<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToEscalaJornadasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('escala_jornadas', function (Blueprint $table) {
            $table->foreign('escala_id')->references('id')->on('empresa_escalas')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('escala_jornadas', function (Blueprint $table) {
            $table->dropForeign('escala_jornadas_escala_id_foreign');
        });
    }
}
