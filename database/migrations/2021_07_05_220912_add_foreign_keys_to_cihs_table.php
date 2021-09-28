<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToCihsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cihs', function (Blueprint $table) {
            $table->foreign('area_id')->references('id')->on('area_etiquetas')->onUpdate('RESTRICT')->onDelete('SET NULL');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('tag_id')->references('id')->on('cih_tags')->onUpdate('RESTRICT')->onDelete('SET NULL');
            $table->foreign('user_aprovacao_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('user_lancamento_id')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cihs', function (Blueprint $table) {
            $table->dropForeign('cihs_area_id_foreign');
            $table->dropForeign('cihs_cliente_id_foreign');
            $table->dropForeign('cihs_feedback_id_foreign');
            $table->dropForeign('cihs_tag_id_foreign');
            $table->dropForeign('cihs_user_aprovacao_id_foreign');
            $table->dropForeign('cihs_user_lancamento_id_foreign');
        });
    }
}
