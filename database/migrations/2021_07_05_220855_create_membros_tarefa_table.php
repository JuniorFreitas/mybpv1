<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembrosTarefaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('membros_tarefa', function (Blueprint $table) {
            $table->unsignedBigInteger('tarefa_id')->index('membros_tarefa_tarefa_id_foreign');
            $table->unsignedBigInteger('user_id')->index('membros_tarefa_user_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('membros_tarefa');
    }
}
