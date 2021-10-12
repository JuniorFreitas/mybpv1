<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCampoQntLinhasSimuladoPerguntasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('simulado_perguntas', function (Blueprint $table) {
            $table->integer('qnt_linhas')->nullable();
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
            $table->dropColumn('qnt_linhas');
        });
    }
}
