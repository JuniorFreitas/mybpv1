<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldGestorTableCentroCusto extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('centro_custos', function (Blueprint $table) {
            $table->unsignedBigInteger('gestor_id')->nullable()->after('id');
            $table->foreign('gestor_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('centro_custos', function (Blueprint $table) {
            //
        });
    }
}
