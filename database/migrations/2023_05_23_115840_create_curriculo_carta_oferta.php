<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurriculoCartaOferta extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('curriculo_carta_oferta', function (Blueprint $table) {
            $table->id();
            $table->string('token')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->unsignedBigInteger('feedback_id')->nullable();
            $table->unsignedBigInteger('vagas_abertas_id')->nullable();
            $table->unsignedBigInteger('vaga_projeto_id')->nullable();
            $table->unsignedInteger('arquivo_id')->nullable();
            $table->enum('status', ['Pendente Anexo', 'Aguardando RH', 'Aceito pelo RH', 'Recusado pelo RH', 'Expirado'])->default('Pendente Anexo');
            $table->string('local')->default('MYBP');
            $table->json('logs')->nullable();
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('users');
            $table->foreign('curriculo_id')->references('id')->on('curriculos');
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos');
            $table->foreign('vagas_abertas_id')->references('id')->on('vagas_abertas');
            $table->foreign('vaga_projeto_id')->references('id')->on('vaga_projetos');
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('curriculo_carta_oferta');
    }
}
