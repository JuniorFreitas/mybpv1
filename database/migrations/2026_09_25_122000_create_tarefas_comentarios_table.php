<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tarefas_comentarios', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tarefa_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->text('comentario');
            $table->string('tipo', 20)->default('comentario')->index();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tarefa_id')
                ->references('id')
                ->on('tarefas')
                ->onUpdate('RESTRICT')
                ->onDelete('CASCADE');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('RESTRICT')
                ->onDelete('RESTRICT');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tarefas_comentarios');
    }
};
