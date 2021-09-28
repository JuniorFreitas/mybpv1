<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArquivosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('arquivos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('quem_enviou')->nullable()->index('arquivos_quem_enviou_foreign');
            $table->text('nome');
            $table->boolean('imagem');
            $table->string('layout', 25)->nullable();
            $table->string('extensao', 5);
            $table->text('file');
            $table->string('thumb', 100)->nullable();
            $table->bigInteger('bytes');
            $table->boolean('temporario');
            $table->string('chave', 90)->nullable();
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
        Schema::dropIfExists('arquivos');
    }
}
