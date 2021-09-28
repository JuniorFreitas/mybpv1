<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtaReuniaoAssuntosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ata_reuniao_assuntos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ata_reuniao_id')->index('ata_reuniao_assuntos_ata_reuniao_id_foreign');
            $table->longText('assunto');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ata_reuniao_assuntos');
    }
}
