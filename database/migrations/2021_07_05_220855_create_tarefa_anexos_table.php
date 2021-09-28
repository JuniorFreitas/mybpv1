<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTarefaAnexosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tarefa_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('tarefa_id')->index('tarefa_anexos_tarefa_id_foreign');
            $table->unsignedBigInteger('arquivo_id')->index('tarefa_anexos_arquivo_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tarefa_anexos');
    }
}
