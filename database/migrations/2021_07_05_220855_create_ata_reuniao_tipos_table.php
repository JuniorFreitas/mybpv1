<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtaReuniaoTiposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ata_reuniao_tipos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ata_reuniao_id')->index('ata_reuniao_tipos_ata_reuniao_id_foreign');
            $table->string('tipo')->comment('Comentário, Assuntos Pendentes ou Próxima Reunião');
            $table->longText('observacao')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ata_reuniao_tipos');
    }
}
