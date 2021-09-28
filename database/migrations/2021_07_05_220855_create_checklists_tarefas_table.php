<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChecklistsTarefasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('checklists_tarefas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tarefa_id')->index('checklists_tarefas_tarefa_id_foreign');
            $table->text('titulo');
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
        Schema::dropIfExists('checklists_tarefas');
    }
}
