<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToSimuladoRespostasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('simulado_respostas', function (Blueprint $table) {
            $table->foreign('simulado_pergunta_id')->references('id')->on('simulado_perguntas')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('simulado_respostas', function (Blueprint $table) {
            $table->dropForeign('simulado_respostas_simulado_pergunta_id_foreign');
        });
    }
}
