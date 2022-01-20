<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCampoPeriodoFeriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ferias_previstas', function (Blueprint $table) {
            $table->unsignedBigInteger('periodo_aquisitivo_id')->nullable();
            $table->foreign('periodo_aquisitivo_id')->references('id')->on('periodos_aquisitivos');
        });

        Schema::table('ferias_previstas', function (Blueprint $table) {
            $table->renameColumn('utima_data','ultima_data');
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
