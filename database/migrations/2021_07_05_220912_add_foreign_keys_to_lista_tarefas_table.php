<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToListaTarefasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lista_tarefas', function (Blueprint $table) {
            $table->foreign('quadro_id')->references('id')->on('quadros')->onUpdate('RESTRICT')->onDelete('CASCADE');
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
        Schema::table('lista_tarefas', function (Blueprint $table) {
            $table->dropForeign('lista_tarefas_quadro_id_foreign');
            $table->dropForeign('lista_tarefas_user_id_foreign');
        });
    }
}
