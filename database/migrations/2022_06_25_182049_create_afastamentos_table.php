<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAfastamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('afastamentos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('feedback_id');
            $table->unsignedBigInteger('cadastrado_id');
            $table->text('motivo');
            $table->date('data_inicio');
            $table->date('data_fim');
            $table->text('observacao')->nullable();
            $table->timestamps();


            $table->foreign('feedback_id')->references('id')->on('feedback_curriculos');
            $table->foreign('cadastrado_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('afastamentos');
    }
}
