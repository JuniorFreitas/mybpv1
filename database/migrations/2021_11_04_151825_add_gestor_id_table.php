<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGestorIdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('demissao_previstas', function (Blueprint $table) {
            $table->unsignedBigInteger('gestor_id')->nullable();
            $table->foreign('gestor_id')->references('id')->on('users');
        });
        Schema::table('ferias_previstas', function (Blueprint $table) {
            $table->unsignedBigInteger('gestor_id')->nullable();
            $table->foreign('gestor_id')->references('id')->on('users');
        });
        Schema::table('admissoes_previstas', function (Blueprint $table) {
            $table->unsignedBigInteger('gestor_id')->nullable();
            $table->foreign('gestor_id')->references('id')->on('users');
        });
        Schema::table('valor_extra_previstas', function (Blueprint $table) {
            $table->unsignedBigInteger('gestor_id')->nullable();
            $table->foreign('gestor_id')->references('id')->on('users');
        });
        Schema::table('muda_cargo_previstas', function (Blueprint $table) {
            $table->unsignedBigInteger('gestor_id')->nullable();
            $table->foreign('gestor_id')->references('id')->on('users');
        });
        Schema::table('intermitente_fixo_previstas', function (Blueprint $table) {
            $table->unsignedBigInteger('gestor_id')->nullable();
            $table->foreign('gestor_id')->references('id')->on('users');
        });
        Schema::table('transferencia_previstas', function (Blueprint $table) {
            $table->unsignedBigInteger('gestor_id')->nullable();
            $table->foreign('gestor_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('demissao_previstas', function (Blueprint $table) {
            $table->dropForeign('gestor_id');
            $table->dropColumn('gestor_id');
        });
        Schema::table('ferias_previstas', function (Blueprint $table) {
            $table->dropForeign('gestor_id');
            $table->dropColumn('gestor_id');
        });
        Schema::table('admissoes_previstas', function (Blueprint $table) {
            $table->dropForeign('gestor_id');
            $table->dropColumn('gestor_id');
        });
        Schema::table('valor_extra_previstas', function (Blueprint $table) {
            $table->dropForeign('gestor_id');
            $table->dropColumn('gestor_id');
        });
        Schema::table('muda_cargo_previstas', function (Blueprint $table) {
            $table->dropForeign('gestor_id');
            $table->dropColumn('gestor_id');
        });
        Schema::table('intermitente_fixo_previstas', function (Blueprint $table) {
            $table->dropForeign('gestor_id');
            $table->dropColumn('gestor_id');
        });
        Schema::table('transferencia_previstas', function (Blueprint $table) {
            $table->dropForeign('gestor_id');
            $table->dropColumn('gestor_id');
        });
    }
}
