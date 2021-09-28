<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlanejamentoDiarioTarefasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('planejamento_diario_tarefas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('planejamento_id')->index('planejamento_diario_tarefas_planejamento_id_foreign');
            $table->string('tarefa', 255);
            $table->string('status', 15);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('planejamento_diario_tarefas');
    }
}
