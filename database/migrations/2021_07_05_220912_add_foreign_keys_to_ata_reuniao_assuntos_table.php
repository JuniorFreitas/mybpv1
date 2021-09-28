<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAtaReuniaoAssuntosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ata_reuniao_assuntos', function (Blueprint $table) {
            $table->foreign('ata_reuniao_id')->references('id')->on('ata_reuniaos')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ata_reuniao_assuntos', function (Blueprint $table) {
            $table->dropForeign('ata_reuniao_assuntos_ata_reuniao_id_foreign');
        });
    }
}
