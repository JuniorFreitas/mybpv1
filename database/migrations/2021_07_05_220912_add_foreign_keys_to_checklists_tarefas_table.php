<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToChecklistsTarefasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checklists_tarefas', function (Blueprint $table) {
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
        Schema::table('checklists_tarefas', function (Blueprint $table) {
            $table->dropForeign('checklists_tarefas_tarefa_id_foreign');
        });
    }
}
