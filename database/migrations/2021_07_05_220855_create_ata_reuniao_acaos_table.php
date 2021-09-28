<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtaReuniaoAcaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ata_reuniao_acaos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('ata_reuniao_id')->index('ata_reuniao_acaos_ata_reuniao_id_foreign');
            $table->string('responsavel');
            $table->string('email');
            $table->longText('acao');
            $table->date('prazo')->nullable();
            $table->boolean('continuo')->nullable();
            $table->string('observacao')->nullable();
            $table->string('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ata_reuniao_acaos');
    }
}
