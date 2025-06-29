<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableAvaliacaoPj extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('avaliacoes_tipos', function (Blueprint $table) {
            $table->boolean('tipo_pj')->default(false)->after('id');
        });
        Schema::table('avaliacao_avaliadores_tipos', function (Blueprint $table) {
            $table->boolean('tipo_pj')->default(false)->after('id');
        });
        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->boolean('tipo_pj')->default(false)->after('id');
        });
        Schema::table('avaliacoes_feedbacks', function (Blueprint $table) {
            $table->boolean('tipo_pj')->default(false)->after('id');
        });
        Schema::table('avaliacoes_resultados', function (Blueprint $table) {
            $table->boolean('tipo_pj')->default(false)->after('id');
        });
        Schema::table('avaliacoes_topicos', function (Blueprint $table) {
            $table->boolean('tipo_pj')->default(false)->after('id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('avaliacoes_tipos', function (Blueprint $table) {
            $table->dropColumn('tipo_pj');
        });
        Schema::table('avaliacao_avaliadores_tipos', function (Blueprint $table) {
            $table->dropColumn('tipo_pj');
        });
        Schema::table('avaliacoes', function (Blueprint $table) {
            $table->dropColumn('tipo_pj');
        });
        Schema::table('avaliacoes_feedbacks', function (Blueprint $table) {
            $table->dropColumn('tipo_pj');
        });
        Schema::table('avaliacoes_resultados', function (Blueprint $table) {
            $table->dropColumn('tipo_pj');
        });
        Schema::table('avaliacoes_topicos', function (Blueprint $table) {
            $table->dropColumn('tipo_pj');
        });
    }
}
