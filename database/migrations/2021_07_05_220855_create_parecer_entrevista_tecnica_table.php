<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParecerEntrevistaTecnicaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parecer_entrevista_tecnica', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable()->index('parecer_entrevista_tecnica_feedback_id_foreign');
            $table->string('tempo_funcao')->nullable();
            $table->boolean('trabalhou_alumar')->nullable();
            $table->boolean('indicado')->nullable();
            $table->string('indicado_por')->nullable();
            $table->boolean('rota')->nullable();
            $table->boolean('ssma')->nullable();
            $table->text('ssma_ex')->nullable();
            $table->boolean('roupa_pvc')->nullable();
            $table->string('roupa_pvc_ex')->nullable();
            $table->boolean('roupa_pvc_dificuldade')->nullable();
            $table->boolean('turno')->nullable();
            $table->boolean('trabalhou_mecanico_manutencao')->nullable();
            $table->text('trabalhou_mecanico_manutencao_ex')->nullable();
            $table->boolean('trabalhou_raquete_produto_quimico')->nullable();
            $table->text('trabalhou_raquete_produto_quimico_ex')->nullable();
            $table->text('tipos_de_talha')->nullable();
            $table->boolean('fechamento_flange')->nullable();
            $table->text('fechamento_flange_ex')->nullable();
            $table->text('milimetros_polegada')->nullable();
            $table->boolean('manuseio_macarico')->nullable();
            $table->text('manuseio_macarico_ex')->nullable();
            $table->boolean('trocou_valvulas')->nullable();
            $table->text('trocou_valvulas_ex')->nullable();
            $table->text('ferramentas_elevacao_carga')->nullable();
            $table->boolean('opera_plat_movel')->nullable();
            $table->text('opera_plat_movel_ex')->nullable();
            $table->boolean('opera_plat_ponte')->nullable();
            $table->text('opera_plat_onte_ex')->nullable();
            $table->boolean('experiencia_cargas_rigger')->nullable();
            $table->text('experiencia_cargas_rigger_ex')->nullable();
            $table->boolean('trabalhou_overhaul')->nullable();
            $table->text('trabalhou_overhaul_ex')->nullable();
            $table->boolean('abertura_tubo_seis_polegada')->nullable();
            $table->boolean('vareta_seis_polegada')->nullable();
            $table->boolean('filete_acabemento')->nullable();
            $table->text('observacao')->nullable();
            $table->string('indicado_area')->nullable();
            $table->string('resultado_final', 255)->nullable();
            $table->integer('nota')->nullable()->default(1);
            $table->unsignedBigInteger('entrevistado_por')->nullable()->index('parecer_entrevista_tecnica_entrevistado_por_foreign');
            $table->string('quem_entrevistou')->nullable();
            $table->timestamps();
            $table->string('tipo_contratacao')->nullable();
            $table->longText('texto_livre')->nullable();
            $table->string('tipo_entrevista')->default('Parada');
            $table->unsignedBigInteger('formulario_id')->nullable()->index('parecer_entrevista_tecnica_formulario_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parecer_entrevista_tecnica');
    }
}
