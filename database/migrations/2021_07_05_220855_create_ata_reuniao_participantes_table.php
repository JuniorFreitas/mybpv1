<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtaReuniaoParticipantesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ata_reuniao_participantes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ata_reuniao_id')->index('ata_reuniao_participantes_ata_reuniao_id_foreign');
            $table->string('nome')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index('ata_reuniao_participantes_user_id_foreign');
            $table->string('funcao');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ata_reuniao_participantes');
    }
}
