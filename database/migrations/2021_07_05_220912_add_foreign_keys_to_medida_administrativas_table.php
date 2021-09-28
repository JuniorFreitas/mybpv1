<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToMedidaAdministrativasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('medida_administrativas', function (Blueprint $table) {
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
        Schema::table('medida_administrativas', function (Blueprint $table) {
            $table->dropForeign('medida_administrativas_feedback_id_foreign');
            $table->dropForeign('medida_administrativas_user_id_foreign');
        });
    }
}
