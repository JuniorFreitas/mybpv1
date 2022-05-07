<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldVagaProjetoIdTableFeedbackCurriculos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feedback_curriculos', function (Blueprint $table) {
            $table->unsignedBigInteger('vaga_projeto_id')->after('vagas_abertas_id')->nullable();

            $table->foreign('vaga_projeto_id')->references('id')->on('vaga_projetos')->onDelete(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('feedback_curriculos', function (Blueprint $table) {
            //
        });
    }
}
