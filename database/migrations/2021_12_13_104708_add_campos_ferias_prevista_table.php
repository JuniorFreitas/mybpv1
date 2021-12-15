<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCamposFeriasPrevistaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ferias_previstas', function (Blueprint $table) {
            $table->string('periodo_aquisitivo')->nullable();
            $table->date('utima_data')->nullable();
            $table->string('mes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ferias_previstas', function (Blueprint $table) {
            $table->dropColumn('periodo_aquisitivo');
            $table->dropColumn('utima_data');
            $table->dropColumn('mes');
        });
    }
}
