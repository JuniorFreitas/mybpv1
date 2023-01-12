<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAcessarareaportoAvaliacaopsicologicaParaAdmissoesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('admissoes', function (Blueprint $table) {
            $table->string('acessar_area_porto')->nullable();
            $table->string('avaliacao_psicologica')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('admissoes', function (Blueprint $table) {
            $table->dropColumn('acessar_area_porto');
            $table->dropColumn('avaliacao_psicologica');
        });
    }
}
