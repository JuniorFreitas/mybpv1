<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToSetorAlternativasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('setor_alternativas', function (Blueprint $table) {
            $table->foreign('alternativa_id')->references('id')->on('alternativa_formularios')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('setor_id')->references('id')->on('setores_formularios')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('setor_alternativas', function (Blueprint $table) {
            $table->dropForeign('setor_alternativas_alternativa_id_foreign');
            $table->dropForeign('setor_alternativas_setor_id_foreign');
        });
    }
}
