<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAtaReuniaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ata_reuniaos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quem_cadastrou')->index('ata_reuniaos_quem_cadastrou_foreign')->comment('Usuario da sessão');
            $table->string('local');
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ata_reuniaos');
    }
}
