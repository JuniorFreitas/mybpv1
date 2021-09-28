<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToSimuladoCandidatoRespostasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('simulado_candidato_respostas', function (Blueprint $table) {
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('simulado_pergunta_id')->references('id')->on('simulado_perguntas')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('simulado_resposta_id')->references('id')->on('simulado_respostas')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('simulado_vaga_id')->references('id')->on('simulado_vagas')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('simulado_candidato_respostas', function (Blueprint $table) {
            $table->dropForeign('simulado_candidato_respostas_feedback_id_foreign');
            $table->dropForeign('simulado_candidato_respostas_simulado_pergunta_id_foreign');
            $table->dropForeign('simulado_candidato_respostas_simulado_resposta_id_foreign');
            $table->dropForeign('simulado_candidato_respostas_simulado_vaga_id_foreign');
        });
    }
}
