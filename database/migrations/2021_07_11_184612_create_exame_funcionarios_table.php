<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExameFuncionariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exame_funcionarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedInteger('formulario_id');
            $table->unsignedBigInteger('feedback_id');
            $table->json('respostas');
            $table->unsignedBigInteger('empresa_exame_id');
            $table->unsignedBigInteger('user_encaminhou_id');
            $table->uuid('token');
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('users')->onDelete('CASCADE');
            $table->foreign('formulario_id')->references('id')->on('formularios');
            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos');
            $table->foreign('empresa_exame_id')->references('id')->on('empresa_exames');
            $table->foreign('user_encaminhou_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exame_funcionarios');
    }
}
