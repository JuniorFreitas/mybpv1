<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChecklistsTarefaItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checklists_tarefa_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('checklist_id')->index('checklists_tarefa_items_checklist_id_foreign');
            $table->text('titulo');
            $table->boolean('concluido')->default(0);
            $table->unsignedBigInteger('ordem');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('checklists_tarefa_items');
    }
}
