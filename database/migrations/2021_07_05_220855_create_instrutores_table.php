<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstrutoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instrutores', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->unsignedBigInteger('arquivo_id')->nullable()->index('instrutores_arquivo_id_foreign');
            $table->text('assinatura')->nullable();
            $table->string('cargo', 255)->nullable();
            $table->string('registro', 255)->nullable();
            $table->unsignedBigInteger('ativo');
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
        Schema::dropIfExists('instrutores');
    }
}
