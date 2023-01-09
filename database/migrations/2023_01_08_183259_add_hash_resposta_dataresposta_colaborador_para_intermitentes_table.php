<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHashRespostaDatarespostaColaboradorParaIntermitentesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('intermitentes', function (Blueprint $table) {
            $table->string('hash_colaborador');
            $table->string('resposta_colaborador')->nullable();
            $table->dateTime('data_resposta_colaborador')->nullable();
            $table->unsignedBigInteger('centro_custo_id');
            $table->foreign('centro_custo_id')->references('id')->on('centro_custos')->cascadeOnDelete();
            $table->unsignedInteger('prazo_resposta');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('intermitentes', function (Blueprint $table) {
            $table->dropColumn('hash_colaborador');
            $table->dropColumn('resposta_colaborador');
            $table->dropColumn('data_resposta_colaborador');
            $table->dropColumn('centro_custo_id');
            $table->dropColumn('prazo_resposta');
        });
    }
}
