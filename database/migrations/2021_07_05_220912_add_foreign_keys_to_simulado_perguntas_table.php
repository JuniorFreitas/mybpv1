<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToSimuladoPerguntasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('simulado_perguntas', function (Blueprint $table) {
            $table->foreign('simulado_id')->references('id')->on('simulados')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('simulado_perguntas', function (Blueprint $table) {
            $table->dropForeign('simulado_perguntas_simulado_id_foreign');
        });
    }
}
