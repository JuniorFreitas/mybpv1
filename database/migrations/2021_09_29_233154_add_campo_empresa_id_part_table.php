<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCampoEmpresaIdPartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vagas_abertas', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned()->change();
        });

        Schema::table('simulados', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('users');
        });

        Schema::table('simulado_vagas', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('users');
            $table->unsignedBigInteger('vagas_abertas_id')->nullable();
            $table->foreign('vagas_abertas_id')->references('id')->on('vagas_abertas');
        });

        Schema::table('simulado_candidatos', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('users');
        });

        Schema::table('feedback_curriculos', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('users');
            $table->unsignedBigInteger('vagas_abertas_id')->nullable();
            $table->foreign('vagas_abertas_id')->references('id')->on('vagas_abertas');
        });

        Schema::create('curriculo_vaga_empresa', function (Blueprint $table) {
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->foreign('curriculo_id')->references('id')->on('curriculos');
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('users');
            $table->unsignedBigInteger('vagas_abertas_id')->nullable();
            $table->foreign('vagas_abertas_id')->references('id')->on('vagas_abertas');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('simulados', function (Blueprint $table) {
            $table->dropForeign('empresa_id');
            $table->dropColumn('empresa_id');
        });
        Schema::table('simulado_vagas', function (Blueprint $table) {
            $table->dropForeign('empresa_id');
            $table->dropColumn('empresa_id');
            $table->dropForeign('vagas_abertas_id');
            $table->dropColumn('vagas_abertas_id');
        });
        Schema::table('simulado_candidatos', function (Blueprint $table) {
            $table->dropForeign('empresa_id');
            $table->dropColumn('empresa_id');
        });
        Schema::table('feedback_curriculos', function (Blueprint $table) {
            $table->dropForeign('empresa_id');
            $table->dropColumn('empresa_id');
            $table->dropForeign('vagas_abertas_id');
            $table->dropColumn('vagas_abertas_id');
        });
        Schema::table('curriculo_vaga_empresa', function (Blueprint $table) {
            $table->dropForeign('curriculo_id');
            $table->dropColumn('curriculo_id');
            $table->dropForeign('empresa_id');
            $table->dropColumn('empresa_id');
            $table->dropForeign('vagas_abertas_id');
            $table->dropColumn('vagas_abertas_id');
        });
    }
}
