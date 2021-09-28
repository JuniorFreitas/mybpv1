<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToMotivoRescisaoCurriculoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('motivo_rescisao_curriculo', function (Blueprint $table) {
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('motivo_id')->references('id')->on('motivo_rescisao')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('motivo_rescisao_curriculo', function (Blueprint $table) {
            $table->dropForeign('motivo_rescisao_curriculo_feedback_id_foreign');
            $table->dropForeign('motivo_rescisao_curriculo_motivo_id_foreign');
        });
    }
}
