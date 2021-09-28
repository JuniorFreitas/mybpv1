<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogTarefasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('log_tarefas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tarefa_id')->index('log_tarefas_tarefa_id_foreign');
            $table->unsignedBigInteger('lista_anterior')->index('log_tarefas_lista_anterior_foreign');
            $table->unsignedBigInteger('lista_atual')->index('log_tarefas_lista_atual_foreign');
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
        Schema::dropIfExists('log_tarefas');
    }
}
