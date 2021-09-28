<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToTipoContratacaosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tipo_contratacaos', function (Blueprint $table) {
            $table->foreign('gestor_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('requisicao_vaga_id')->references('id')->on('requisicao_vagas')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tipo_contratacaos', function (Blueprint $table) {
            $table->dropForeign('tipo_contratacaos_gestor_id_foreign');
            $table->dropForeign('tipo_contratacaos_requisicao_vaga_id_foreign');
        });
    }
}
