<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParecerEntrevistasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parecer_entrevistas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo');
            $table->string('rota_bairro');
            $table->boolean('rota_bairro_op');
            $table->string('composicao_familiar');
            $table->boolean('experiencia_alumar');
            $table->boolean('disponibilidade_horaextra');
            $table->string('disponibilidade_turno');
            $table->boolean('acidente_trabalho');
            $table->string('acidente_trabalho_desc');
            $table->boolean('afastamento_inss_trabalho');
            $table->string('afastamento_inss_desc');
            $table->string('situacoes_saude');
            $table->boolean('parada');
            $table->boolean('fixo');
            $table->text('obs');
            $table->string('comportamento_seguro')->comment('SIM, NÃO, RAZOÁVEL');
            $table->string('energia_trabalho')->comment('SIM, NÃO, RAZOÁVEL');
            $table->string('postura')->comment('SIM, NÃO, RAZOÁVEL');
            $table->string('tec_adequacao_vaga')->comment('SIM, NÃO, RAZOÁVEL');
            $table->text('tec_obs');
            $table->boolean('rota_transporte');
            $table->string('parecer_final_rh')->comment('FAVORÁVEL, RESTRIÇÃO, DESFAVORÁVEL');
            $table->string('parecer_final_tecnico')->comment('FAVORÁVEL, RESTRIÇÃO, DESFAVORÁVEL');
            $table->unsignedBigInteger('entrevistador_rh');
            $table->unsignedBigInteger('entrevistador_tecnico');
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
        Schema::dropIfExists('parecer_entrevistas');
    }
}
