<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToTipoAvisoCurriculoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tipo_aviso_curriculo', function (Blueprint $table) {
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('tipo_aviso_id')->references('id')->on('tipo_aviso')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tipo_aviso_curriculo', function (Blueprint $table) {
            $table->dropForeign('tipo_aviso_curriculo_feedback_id_foreign');
            $table->dropForeign('tipo_aviso_curriculo_tipo_aviso_id_foreign');
        });
    }
}
