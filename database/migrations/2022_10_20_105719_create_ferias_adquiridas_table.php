<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeriasAdquiridasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ferias_adquiridas', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('admissao_id');
            $table->string('periodo_gozado');
            $table->integer('qnt_dias');
            $table->date('data_saida');
            $table->date('data_retorno');
            $table->string('proximo_periodo');
            $table->date('data_limite');
            $table->unsignedBigInteger('user_cadastrou_id');
            $table->unsignedBigInteger('user_alterou_id')->nullable();
            $table->timestamps();

            $table->foreign('admissao_id')->references('id')->on('admissoes')->cascadeOnDelete();
            $table->foreign('user_cadastrou_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('user_alterou_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ferias_adquiridas');
    }
}
