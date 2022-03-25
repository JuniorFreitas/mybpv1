<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRecuperacaoSenhasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recuperacao_senhas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('user_id');
            $table->text('token');
            $table->ipAddress('ip_solicitacao');
            $table->dateTime('solicitacao');
            $table->dateTime('expiracao');
            $table->ipAddress('ip_recuperacao')->nullable();
            $table->dateTime('recuperacao')->nullable();
            $table->boolean('recuperado');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recuperacao_senhas');
    }
}
