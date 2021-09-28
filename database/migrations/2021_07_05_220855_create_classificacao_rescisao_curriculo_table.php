<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassificacaoRescisaoCurriculoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('classificacao_rescisao_curriculo', function (Blueprint $table) {
            $table->unsignedBigInteger('classificacao_id')->index('classificacao_rescisao_curriculo_classificacao_id_foreign');
            $table->unsignedBigInteger('feedback_id')->nullable()->index('classificacao_rescisao_curriculo_feedback_id_foreign');
            $table->text('observacoes')->nullable();
            $table->string('quem_classificou')->nullable();
            $table->date('data_afastamento')->nullable();
            $table->string('preenchido_por')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index('classificacao_rescisao_curriculo_user_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('classificacao_rescisao_curriculo');
    }
}
