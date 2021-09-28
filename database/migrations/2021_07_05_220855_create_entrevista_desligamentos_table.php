<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEntrevistaDesligamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entrevista_desligamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable()->index('entrevista_desligamentos_feedback_id_foreign');
            $table->string('superior_imediato')->nullable();
            $table->longText('motivo')->nullable();
            $table->longText('trabalharia_novamente')->nullable();
            $table->longText('contr_melhoria')->nullable();
            $table->string('relacao_interpessoal')->nullable();
            $table->string('recursos_fisicos')->nullable();
            $table->string('valores_normas')->nullable();
            $table->string('planejamento')->nullable();
            $table->string('sob_superior_imediato')->nullable();
            $table->string('direcao_empresa')->nullable();
            $table->string('oportunidades')->nullable();
            $table->string('salario_beneficio')->nullable();
            $table->string('atividade')->nullable();
            $table->longText('comentarios')->nullable();
            $table->longText('parecer_entrevistador')->nullable();
            $table->boolean('pode_voltar');
            $table->longText('porque_pode_voltar')->nullable();
            $table->string('quem_entrevistou');
            $table->unsignedBigInteger('user_entrevista')->index('entrevista_desligamentos_user_entrevista_foreign');
            $table->dateTime('data_entrevista')->nullable();
            $table->string('preenchido_por')->nullable();
            $table->timestamps();
            $table->unsignedBigInteger('formulario_id')->nullable()->index('entrevista_desligamentos_formulario_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('entrevista_desligamentos');
    }
}
