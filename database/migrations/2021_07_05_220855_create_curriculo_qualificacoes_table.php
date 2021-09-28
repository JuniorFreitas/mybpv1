<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCurriculoQualificacoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('curriculo_qualificacoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('curriculo_id')->index('curriculo_qualificacoes_curriculo_foreign');
            $table->string('nome');
            $table->string('instituicao');
            $table->string('mes_conclusao');
            $table->year('ano_conclusao');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('curriculo_qualificacoes');
    }
}
