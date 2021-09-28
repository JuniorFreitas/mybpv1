<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExameTreinamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exame_treinamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id')->index('exame_treinamentos_feedback_id_foreign');
            $table->boolean('exame_realizado');
            $table->date('data_realizado')->nullable();
            $table->string('tipo_exame')->nullable();
            $table->boolean('trabalho_altura')->nullable();
            $table->boolean('espaco_confinado')->nullable();
            $table->unsignedBigInteger('user_id')->index('exame_treinamentos_user_id_foreign');
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
        Schema::dropIfExists('exame_treinamentos');
    }
}
