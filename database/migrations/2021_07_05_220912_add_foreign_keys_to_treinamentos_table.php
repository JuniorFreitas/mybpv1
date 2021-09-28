<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToTreinamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('treinamentos', function (Blueprint $table) {
            $table->foreign('cadastrou')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('enviou_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('gerou_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('treinamentos', function (Blueprint $table) {
            $table->dropForeign('treinamentos_cadastrou_foreign');
            $table->dropForeign('treinamentos_enviou_id_foreign');
            $table->dropForeign('treinamentos_feedback_id_foreign');
            $table->dropForeign('treinamentos_gerou_id_foreign');
        });
    }
}
