<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToRespostaAlternativasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('resposta_alternativas', function (Blueprint $table) {
            $table->foreign('alternativa_id')->references('id')->on('alternativa_formularios')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('link_id')->references('id')->on('alternativa_formularios')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('resposta_alternativas', function (Blueprint $table) {
            $table->dropForeign('resposta_alternativas_alternativa_id_foreign');
            $table->dropForeign('resposta_alternativas_link_id_foreign');
        });
    }
}
