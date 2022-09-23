<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCamposNovos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('curriculos', function (Blueprint $table) {
            $table->string('estado_civil')->after('nome')->nullable();
            $table->date('cnh_vencimento')->after('cnh')->nullable();
        });

        Schema::table('admissoes', function (Blueprint $table) {
            $table->string('matricula')->after('id')->nullable();
            $table->unsignedBigInteger('centro_custo_id')->after('id')->nullable();

            $table->foreign('centro_custo_id')->references('id')->on('centro_custos')->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('curriculos', function (Blueprint $table) {
            //
        });
    }
}
