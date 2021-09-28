<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToOcorrenciasRespostasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ocorrencias_respostas', function (Blueprint $table) {
            $table->foreign('ocorrencia_id')->references('id')->on('ocorrencias')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ocorrencias_respostas', function (Blueprint $table) {
            $table->dropForeign('ocorrencias_respostas_ocorrencia_id_foreign');
            $table->dropForeign('ocorrencias_respostas_user_id_foreign');
        });
    }
}
