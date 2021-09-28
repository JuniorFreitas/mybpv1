<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOcorrenciasAnexosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ocorrencias_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('arquivo_id')->index('ocorrencias_anexos_arquivo_id_foreign');
            $table->unsignedBigInteger('resposta_id')->index('ocorrencias_anexos_resposta_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ocorrencias_anexos');
    }
}
