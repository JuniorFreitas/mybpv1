<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToClassificacaoRescisaoCurriculoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('classificacao_rescisao_curriculo', function (Blueprint $table) {
            $table->foreign('classificacao_id')->references('id')->on('classificacao_rescisao')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
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
        Schema::table('classificacao_rescisao_curriculo', function (Blueprint $table) {
            $table->dropForeign('classificacao_rescisao_curriculo_classificacao_id_foreign');
            $table->dropForeign('classificacao_rescisao_curriculo_feedback_id_foreign');
            $table->dropForeign('classificacao_rescisao_curriculo_user_id_foreign');
        });
    }
}
