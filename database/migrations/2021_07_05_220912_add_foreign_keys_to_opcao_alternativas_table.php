<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToOpcaoAlternativasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opcao_alternativas', function (Blueprint $table) {
            $table->foreign('alternativa_id')->references('id')->on('alternativa_formularios')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('opcao_alternativas', function (Blueprint $table) {
            $table->dropForeign('opcao_alternativas_alternativa_id_foreign');
        });
    }
}
