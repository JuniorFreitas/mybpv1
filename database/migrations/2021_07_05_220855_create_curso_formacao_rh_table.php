<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCursoFormacaoRhTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('curso_formacao_rh', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->nullable()->index('curso_formacao_rh_feedback_id_foreign');
            $table->string('curso');
            $table->string('instituicao');
            $table->date('emissao');
            $table->date('validade')->nullable();
            $table->boolean('certificado')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('curso_formacao_rh');
    }
}
