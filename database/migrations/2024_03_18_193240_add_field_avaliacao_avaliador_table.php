<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldAvaliacaoAvaliadorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('avaliacoes_feedbacks', function (Blueprint $table) {
            $table->unsignedBigInteger('avaliacao_tipo_id')->nullable();
            $table->foreign('avaliacao_tipo_id')->references('id')->on('avaliacao_avaliadores_tipos')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('avaliacoes_feedbacks', function (Blueprint $table) {
            $table->dropIndex('avaliacao_tipo_id');
            $table->dropColumn('avaliacao_tipo_id');
        });
    }
}
