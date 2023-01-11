<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAreaCentroCustoIdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ata_reuniaos', function (Blueprint $table) {
            $table->unsignedInteger('area_etiqueta_id')->nullable();
            $table->unsignedBigInteger('centro_custo_id')->nullable();
            $table->foreign('area_etiqueta_id')->references('id')->on('area_etiquetas')->cascadeOnDelete();
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
        Schema::table('ata_reuniaos', function (Blueprint $table) {
            $table->dropColumn('area_etiqueta_id');
            $table->dropColumn('centro_custo_id');
        });
    }
}
