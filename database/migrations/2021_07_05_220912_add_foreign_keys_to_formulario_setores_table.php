<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToFormularioSetoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('formulario_setores', function (Blueprint $table) {
            $table->foreign('formulario_id')->references('id')->on('formularios')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('setores_id')->references('id')->on('setores_formularios')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('formulario_setores', function (Blueprint $table) {
            $table->dropForeign('formulario_setores_formulario_id_foreign');
            $table->dropForeign('formulario_setores_setores_id_foreign');
        });
    }
}
