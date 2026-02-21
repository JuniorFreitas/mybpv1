<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNpsCicloIdToNpsRespostasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('nps_respostas', function (Blueprint $table) {
            $table->unsignedBigInteger('nps_ciclo_id')->nullable()->after('empresa_id');
            $table->foreign('nps_ciclo_id')->references('id')->on('nps_ciclos')->nullOnDelete();
            $table->index('nps_ciclo_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('nps_respostas', function (Blueprint $table) {
            $table->dropForeign(['nps_ciclo_id']);
            $table->dropIndex(['nps_ciclo_id']);
            $table->dropColumn('nps_ciclo_id');
        });
    }
}
