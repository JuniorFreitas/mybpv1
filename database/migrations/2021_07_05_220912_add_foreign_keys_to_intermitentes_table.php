<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToIntermitentesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('intermitentes', function (Blueprint $table) {
            $table->foreign('area_id')->references('id')->on('area_etiquetas')->onUpdate('RESTRICT')->onDelete('SET NULL');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('tipo_id')->references('id')->on('intermitente_tipos')->onUpdate('RESTRICT')->onDelete('SET NULL');
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
        Schema::table('intermitentes', function (Blueprint $table) {
            $table->dropForeign('intermitentes_area_id_foreign');
            $table->dropForeign('intermitentes_cliente_id_foreign');
            $table->dropForeign('intermitentes_feedback_id_foreign');
            $table->dropForeign('intermitentes_tipo_id_foreign');
            $table->dropForeign('intermitentes_user_aprovacao_id_foreign');
            $table->dropForeign('intermitentes_user_lancamento_id_foreign');
        });
    }
}
