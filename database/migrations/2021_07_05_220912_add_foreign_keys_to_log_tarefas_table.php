<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToLogTarefasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('log_tarefas', function (Blueprint $table) {
            $table->foreign('lista_anterior')->references('id')->on('lista_tarefas')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('lista_atual')->references('id')->on('lista_tarefas')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('tarefa_id')->references('id')->on('tarefas')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log_tarefas', function (Blueprint $table) {
            $table->dropForeign('log_tarefas_lista_anterior_foreign');
            $table->dropForeign('log_tarefas_lista_atual_foreign');
            $table->dropForeign('log_tarefas_tarefa_id_foreign');
        });
    }
}
