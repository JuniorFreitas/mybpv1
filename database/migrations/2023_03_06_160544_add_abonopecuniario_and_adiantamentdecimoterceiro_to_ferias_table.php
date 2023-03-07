<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAbonopecuniarioAndAdiantamentdecimoterceiroToFeriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ferias', function (Blueprint $table) {
            $table->boolean('abono_pecuniario')->default(false);
            $table->boolean('adiantamento_decimo_terceiro')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ferias', function (Blueprint $table) {
            $table->dropColumn('abono_pecuniario');
            $table->dropColumn('adiantamento_decimo_terceiro');
        });
    }
}
