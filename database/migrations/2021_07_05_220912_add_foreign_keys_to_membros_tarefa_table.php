<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToMembrosTarefaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('membros_tarefa', function (Blueprint $table) {
            $table->foreign('tarefa_id')->references('id')->on('tarefas')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('membros_tarefa', function (Blueprint $table) {
            $table->dropForeign('membros_tarefa_tarefa_id_foreign');
            $table->dropForeign('membros_tarefa_user_id_foreign');
        });
    }
}
