<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToParecerEntrevistaTecnicaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parecer_entrevista_tecnica', function (Blueprint $table) {
            $table->foreign('entrevistado_por')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('formulario_id')->references('id')->on('formularios')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parecer_entrevista_tecnica', function (Blueprint $table) {
            $table->dropForeign('parecer_entrevista_tecnica_entrevistado_por_foreign');
            $table->dropForeign('parecer_entrevista_tecnica_feedback_id_foreign');
            $table->dropForeign('parecer_entrevista_tecnica_formulario_id_foreign');
        });
    }
}
