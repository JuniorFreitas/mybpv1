<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToChecklistsTarefaItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('checklists_tarefa_items', function (Blueprint $table) {
            $table->foreign('checklist_id')->references('id')->on('checklists_tarefas')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('checklists_tarefa_items', function (Blueprint $table) {
            $table->dropForeign('checklists_tarefa_items_checklist_id_foreign');
        });
    }
}
