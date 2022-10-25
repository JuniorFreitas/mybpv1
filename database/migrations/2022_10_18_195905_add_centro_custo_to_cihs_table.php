<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCentroCustoToCihsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cihs', function (Blueprint $table) {
            $table->unsignedBigInteger('centro_custo_id')->nullable();
            $table->foreign('centro_custo_id')->references('id')->on('centro_custos');
            $table->string('centro_custo_outro')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cihs', function (Blueprint $table) {
            $table->dropForeign('centro_custo_id');
            $table->dropColumn('centro_custo_id');
            $table->dropColumn('centro_custo_outro');
        });
    }
}
