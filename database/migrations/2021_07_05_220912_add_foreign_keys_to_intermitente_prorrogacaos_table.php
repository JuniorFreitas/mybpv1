<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToIntermitenteProrrogacaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('intermitente_prorrogacaos', function (Blueprint $table) {
            $table->foreign('intermitente_id')->references('id')->on('intermitentes')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('intermitente_prorrogacaos', function (Blueprint $table) {
            $table->dropForeign('intermitente_prorrogacaos_intermitente_id_foreign');
        });
    }
}
