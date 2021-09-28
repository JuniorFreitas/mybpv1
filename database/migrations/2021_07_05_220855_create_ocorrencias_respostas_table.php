<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOcorrenciasRespostasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ocorrencias_respostas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ocorrencia_id')->index('ocorrencias_respostas_ocorrencia_id_foreign');
            $table->unsignedBigInteger('user_id')->index('ocorrencias_respostas_user_id_foreign');
            $table->text('resposta');
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
        Schema::dropIfExists('ocorrencias_respostas');
    }
}
