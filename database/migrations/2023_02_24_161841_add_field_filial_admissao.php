<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldFilialAdmissao extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admissoes', function (Blueprint $table) {
            $table->boolean('filial')->default(false)->after('feedback_id');
            $table->unsignedBigInteger('centro_custo_filial_id')->nullable()->after('filial');

            $table->foreign('centro_custo_filial_id')->references('id')->on('centro_custo_filials')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admissoes', function (Blueprint $table) {
            //
        });
    }
}
