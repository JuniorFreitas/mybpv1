<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToGestorRhsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('gestor_rhs', function (Blueprint $table) {
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('formulario_id')->references('id')->on('formularios')->onUpdate('RESTRICT')->onDelete('CASCADE');
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
        Schema::table('gestor_rhs', function (Blueprint $table) {
            $table->dropForeign('gestor_rhs_feedback_id_foreign');
            $table->dropForeign('gestor_rhs_formulario_id_foreign');
            $table->dropForeign('gestor_rhs_user_id_foreign');
        });
    }
}
