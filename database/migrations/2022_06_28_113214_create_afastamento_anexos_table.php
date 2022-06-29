<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAfastamentoAnexosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('afastamento_anexos', function (Blueprint $table) {
            $table->unsignedBigInteger('afastamento_id');
            $table->unsignedBigInteger('arquivo_id');
            $table->foreign('arquivo_id')->references('id')->on('arquivos')->onDelete('CASCADE');
            $table->foreign('afastamento_id')->references('id')->on('afastamentos')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('afastamento_anexos');
    }
}
