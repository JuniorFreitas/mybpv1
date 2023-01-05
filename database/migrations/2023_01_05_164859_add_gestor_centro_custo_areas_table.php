<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGestorCentroCustoAreasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('area_etiquetas', function (Blueprint $table) {
            $table->unsignedBigInteger('gestor_id')->nullable();
            $table->foreign('gestor_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unsignedBigInteger('centro_custo_id')->nullable();
            $table->foreign('centro_custo_id')->references('id')->on('centro_custos')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('area_etiquetas', function (Blueprint $table) {
            $table->dropColumn('gestor_id');
            $table->dropColumn('centro_custo_id');
        });
    }
}
