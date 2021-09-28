<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFormularioAvaliacaoAnualsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('formulario_avaliacao_anuals', function (Blueprint $table) {
            $table->id();
            $table->longText('pergunta');
            $table->unsignedBigInteger('topicos_id')->index('formulario_avaliacao_anuals_topicos_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('formulario_avaliacao_anuals');
    }
}
