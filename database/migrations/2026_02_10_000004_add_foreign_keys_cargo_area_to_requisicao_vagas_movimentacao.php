<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysCargoAreaToRequisicaoVagasMovimentacao extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requisicao_vagas_movimentacao', function (Blueprint $table) {
            // Adicionar constraints para cargo_id e area_id
            // $table->unsignedInteger('cargo_id')->references('id')->on('vagas')->onDelete('restrict');
            // $table->unsignedInteger('area_id')->references('id')->on('area_etiquetas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requisicao_vagas_movimentacao', function (Blueprint $table) {
            $table->dropForeign(['cargo_id']);
            $table->dropForeign(['area_id']);
        });
    }
}
