<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableEscalaJornadas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('escala_jornadas', function (Blueprint $table) {
            $table->unsignedBigInteger('ocorrencia_id')->after('escala_id');
            $table->foreign('ocorrencia_id')->references('id')->on('ocorrencias_jornada')->onDelete('cascade');
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
            $table->dropForeign('ocorrencia_id');
            $table->dropColumn('ocorrencia_id');
        });
    }
}
