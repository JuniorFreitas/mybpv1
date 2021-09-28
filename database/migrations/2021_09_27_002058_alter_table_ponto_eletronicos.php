<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTablePontoEletronicos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('ponto_eletronicos');
        Schema::create('ponto_eletronicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('funcionario_id');

            $table->unsignedBigInteger('jornada_id')->nullable();
            $table->unsignedBigInteger('ocorrencia_id');
            $table->integer('duracao'); // minutos trabalhados esperados
            $table->integer('duracao_normal')->nullable(); // minutos trabalhados
            $table->integer('duracao_extra')->nullable(); // minutos trabalhados de horas extra
            $table->integer('duracao_noturna')->nullable(); // minutos trabalhados de horas noturnas
            $table->string('tipo_frequencia')->default('hora_extra'); // 'hora_extra','banco_horas','hibrido'
            $table->integer('tempo_limite_falta')->default('60'); // em minutos
            $table->integer('tempo_limite_saida')->default('60'); // em minutos
            $table->integer('limite_tolerancia')->default('15'); // em minutos
            $table->text('justificativa')->nullable();
            $table->boolean('verificado')->default(false);

            $table->foreign('empresa_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('funcionario_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('jornada_id')->references('id')->on('escala_jornadas')->onDelete('set null');
            $table->foreign('ocorrencia_id')->references('id')->on('ocorrencias_jornada');

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
        Schema::dropIfExists('ponto_eletronicos');
    }
}
