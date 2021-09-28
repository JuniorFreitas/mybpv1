<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAtaReuniaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ata_reuniaos', function (Blueprint $table) {
            $table->foreign('quem_cadastrou')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ata_reuniaos', function (Blueprint $table) {
            $table->dropForeign('ata_reuniaos_quem_cadastrou_foreign');
        });
    }
}
