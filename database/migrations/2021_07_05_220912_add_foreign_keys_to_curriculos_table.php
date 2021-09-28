<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToCurriculosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('curriculos', function (Blueprint $table) {
            $table->foreign('formacao')->references('id')->on('escolaridades')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('municipio_id')->references('id')->on('municipios')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('usuario_lido')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('vaga_pretendida')->references('id')->on('vagas')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('curriculos', function (Blueprint $table) {
            $table->dropForeign('curriculos_formacao_foreign');
            $table->dropForeign('curriculos_id_foreign');
            $table->dropForeign('curriculos_municipio_id_foreign');
            $table->dropForeign('curriculos_usuario_lido_foreign');
            $table->dropForeign('curriculos_vaga_pretendida_foreign');
        });
    }
}
