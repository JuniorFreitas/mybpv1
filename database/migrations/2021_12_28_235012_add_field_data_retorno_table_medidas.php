<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldDataRetornoTableMedidas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('medida_administrativas', function (Blueprint $table) {
            $table->date('data_retorno')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('medida_administrativas', function (Blueprint $table) {
            $table->dropColumn(['data_retorno']);
        });
    }
}
