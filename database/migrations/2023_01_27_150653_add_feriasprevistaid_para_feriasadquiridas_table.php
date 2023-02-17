<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeriasprevistaidParaFeriasadquiridasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ferias_adquiridas', function (Blueprint $table) {
            $table->unsignedBigInteger('ferias_prevista_id')->nullable();
            $table->foreign('ferias_prevista_id')->references('id')->on('ferias_previstas')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ferias_adquiridas', function (Blueprint $table) {
            //
        });
    }
}
