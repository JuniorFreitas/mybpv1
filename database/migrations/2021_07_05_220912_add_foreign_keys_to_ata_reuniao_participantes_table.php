<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToAtaReuniaoParticipantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ata_reuniao_participantes', function (Blueprint $table) {
            $table->foreign('ata_reuniao_id')->references('id')->on('ata_reuniaos')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ata_reuniao_participantes', function (Blueprint $table) {
            $table->dropForeign('ata_reuniao_participantes_ata_reuniao_id_foreign');
            $table->dropForeign('ata_reuniao_participantes_user_id_foreign');
        });
    }
}
