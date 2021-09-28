<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSimuladoCandidatoRespostasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('simulado_candidato_respostas', function (Blueprint $table) {
            $table->unsignedBigInteger('simulado_vaga_id')->index('simulado_candidato_respostas_simulado_vaga_id_foreign');
            $table->unsignedBigInteger('feedback_id')->nullable()->index('simulado_candidato_respostas_feedback_id_foreign');
            $table->unsignedBigInteger('simulado_pergunta_id')->index('simulado_candidato_respostas_simulado_pergunta_id_foreign');
            $table->unsignedBigInteger('simulado_resposta_id')->index('simulado_candidato_respostas_simulado_resposta_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('simulado_candidato_respostas');
    }
}
