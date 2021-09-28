<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParecerRhTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('parecer_rh', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable()->index('parecer_rh_feedback_id_foreign');
            $table->boolean('cnh')->nullable();
            $table->boolean('ex_funcionario')->nullable();
            $table->string('cnh_tipo', 255)->nullable();
            $table->string('rota_bairro', 255)->nullable();
            $table->integer('calca')->nullable();
            $table->integer('bota')->nullable();
            $table->integer('camisa_protecao')->nullable();
            $table->string('camisa_meia', 255)->nullable();
            $table->string('mora_com_quem', 255)->nullable();
            $table->boolean('casado')->nullable();
            $table->string('tempodeconvivencia', 255)->nullable();
            $table->boolean('filhos')->nullable();
            $table->integer('qnt_filhos')->nullable();
            $table->boolean('conjuge_trabalha')->nullable();
            $table->string('trabalho_conjuge')->nullable();
            $table->boolean('religioso')->nullable();
            $table->string('religiao_praticante')->nullable();
            $table->boolean('fuma')->nullable();
            $table->string('frequencia_fuma')->nullable();
            $table->boolean('bebe')->nullable();
            $table->string('frequencia_bebe')->nullable();
            $table->boolean('indicacao')->nullable();
            $table->string('indicado_por')->nullable();
            $table->boolean('alumar_experiencia')->nullable();
            $table->string('alumar_experiencia_area')->nullable();
            $table->boolean('outra_industria_experiencia')->nullable();
            $table->string('outra_industria_nome')->nullable();
            $table->string('grau_instrucao')->nullable();
            $table->boolean('horaextra')->nullable();
            $table->boolean('turnos_seis_por_dois')->nullable();
            $table->boolean('noturno')->nullable();
            $table->boolean('acidente_trabalho')->nullable();
            $table->text('acidente_trabalho_qual')->nullable();
            $table->boolean('afastamento_inss')->nullable();
            $table->text('afastamento_inss_qual')->nullable();
            $table->text('situacao_saude')->nullable();
            $table->string('nr_dez', 255)->nullable();
            $table->string('comportamento_seguro', 255)->nullable();
            $table->string('energia_para_trabalho', 255)->nullable();
            $table->string('postura', 255)->nullable();
            $table->text('historico_profissional')->nullable();
            $table->text('historico_educacional')->nullable();
            $table->text('objetivos_expectativas')->nullable();
            $table->text('auto_imagem')->nullable();
            $table->integer('competencias')->nullable();
            $table->integer('comportamento_etico')->nullable();
            $table->integer('comprometimento')->nullable();
            $table->integer('comunicacao')->nullable();
            $table->integer('cultura_qualidade')->nullable();
            $table->integer('foco_cliente')->nullable();
            $table->integer('iniciativa')->nullable();
            $table->integer('orientacao_resultados')->nullable();
            $table->integer('trabalho_equipe')->nullable();
            $table->string('parecer_final', 255)->nullable();
            $table->string('parecer_final_um', 255)->nullable();
            $table->integer('nota')->nullable()->default(1);
            $table->text('comentarios')->nullable();
            $table->unsignedBigInteger('entrevistador')->nullable()->index('parecer_rh_entrevistador_foreign');
            $table->string('quem_entrevistou')->nullable();
            $table->timestamps();
            $table->string('destro')->nullable();
            $table->string('tipo_entrevista')->default('Parada');
            $table->integer('nota_digitacao')->nullable();
            $table->string('dinamicadegrupo')->nullable();
            $table->string('obs_dinamicadegrupo')->nullable();
            $table->boolean('experiencia_callcenter')->nullable();
            $table->string('disponibilidade_horarios')->nullable();
            $table->boolean('turnos_seis_por_um')->nullable();
            $table->string('horario_preferencial')->nullable();
            $table->text('obs_call')->nullable();
            $table->text('obs_horario')->nullable();
            $table->unsignedBigInteger('formulario_id')->nullable()->index('parecer_rh_formulario_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parecer_rh');
    }
}
