<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTreinamentoEventoAnexosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('treinamento_evento_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('treinamento_evento_id')->index('treinamento_evento_anexos_treinamento_evento_id_foreign');
            $table->unsignedBigInteger('arquivo_id')->index('treinamento_evento_anexos_arquivo_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('treinamento_evento_anexos');
    }
}
