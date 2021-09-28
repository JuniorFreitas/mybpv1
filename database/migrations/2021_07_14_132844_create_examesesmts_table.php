<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExamesesmtsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('examesesmts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exame_funcionario_id');
            $table->unsignedBigInteger('empresa_id');
            $table->boolean('exame_realizado');
            $table->json('resultado');
            $table->date('data_realizacao');
            $table->date('data_vencimento');
            $table->boolean('vencido');
            $table->unsignedBigInteger('user_id');
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
        Schema::dropIfExists('examesesmts');
    }
}
