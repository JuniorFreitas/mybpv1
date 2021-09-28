<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToFormularioAvaliacaoAnualsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('formulario_avaliacao_anuals', function (Blueprint $table) {
            $table->foreign('topicos_id')->references('id')->on('topicos')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('formulario_avaliacao_anuals', function (Blueprint $table) {
            $table->dropForeign('formulario_avaliacao_anuals_topicos_id_foreign');
        });
    }
}
