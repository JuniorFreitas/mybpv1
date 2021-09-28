<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToEtapasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('etapas', function (Blueprint $table) {
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('passo_id')->references('id')->on('etapa_tipos')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('vaga_id')->references('id')->on('vagas')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('etapas', function (Blueprint $table) {
            $table->dropForeign('etapas_feedback_id_foreign');
            $table->dropForeign('etapas_passo_id_foreign');
            $table->dropForeign('etapas_user_id_foreign');
            $table->dropForeign('etapas_vaga_id_foreign');
        });
    }
}
