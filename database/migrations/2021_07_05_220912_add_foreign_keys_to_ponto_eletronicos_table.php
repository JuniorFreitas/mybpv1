<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPontoEletronicosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ponto_eletronicos', function (Blueprint $table) {
            $table->foreign('escala_id')->references('id')->on('empresa_escalas')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('funcionario_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('jornada_id')->references('id')->on('escala_jornadas')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('ocorrencia_jornada_id')->references('id')->on('ocorrencias_jornada')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('periodo_id')->references('id')->on('periodo_jornadas')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ponto_eletronicos', function (Blueprint $table) {
            $table->dropForeign('ponto_eletronicos_escala_id_foreign');
            $table->dropForeign('ponto_eletronicos_funcionario_id_foreign');
            $table->dropForeign('ponto_eletronicos_jornada_id_foreign');
            $table->dropForeign('ponto_eletronicos_ocorrencia_jornada_id_foreign');
            $table->dropForeign('ponto_eletronicos_periodo_id_foreign');
        });
    }
}
