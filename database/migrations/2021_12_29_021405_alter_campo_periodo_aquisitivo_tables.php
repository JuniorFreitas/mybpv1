<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterCampoPeriodoAquisitivoTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ferias_prevista_dados', function (Blueprint $table) {
            $table->renameColumn('periodo_aquisitivo','periodo_aquisitivo_id');
        });

        Schema::table('ferias_prevista_movs', function (Blueprint $table) {
            $table->renameColumn('ultimo_periodo_aquisitivo','ultimo_periodo_aquisitivo_id');
        });
        Schema::table('ferias_prevista_dados', function (Blueprint $table) {
            $table->unsignedBigInteger('periodo_aquisitivo_id')->nullable()->change();
            $table->foreign('periodo_aquisitivo_id')->references('id')->on('periodos_aquisitivos');
        });
        Schema::table('ferias_prevista_movs', function (Blueprint $table) {
            $table->unsignedBigInteger('ultimo_periodo_aquisitivo_id')->nullable()->change();
            $table->foreign('ultimo_periodo_aquisitivo_id')->references('id')->on('periodos_aquisitivos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
