<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTarefasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tarefas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('lista_id')->index('tarefas_lista_id_foreign');
            $table->unsignedBigInteger('user_id')->index('tarefas_user_id_foreign');
            $table->text('titulo');
            $table->text('descricao')->nullable();
            $table->unsignedBigInteger('ordem');
            $table->dateTime('datahora_inicio')->nullable();
            $table->dateTime('datahora_entrega')->nullable();
            $table->dateTime('lembrete')->nullable();
            $table->boolean('concluido')->default(0);
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
        Schema::dropIfExists('tarefas');
    }
}
