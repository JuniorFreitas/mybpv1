<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIntermitenteEvidenciasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('intermitente_evidencias', function (Blueprint $table) {
            $table->unsignedBigInteger('intermitente_id')->index('intermitente_evidencias_intermitente_id_foreign');
            $table->unsignedBigInteger('arquivo_id')->index('intermitente_evidencias_arquivo_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('intermitente_evidencias');
    }
}
