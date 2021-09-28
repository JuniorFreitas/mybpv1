<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntermitenteProrrogacaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('intermitente_prorrogacaos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('intermitente_id')->index('intermitente_prorrogacaos_intermitente_id_foreign');
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->string('solicitante');
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
        Schema::dropIfExists('intermitente_prorrogacaos');
    }
}
