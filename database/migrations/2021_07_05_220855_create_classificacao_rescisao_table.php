<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClassificacaoRescisaoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('classificacao_rescisao', function (Blueprint $table) {
            $table->id();
            $table->string('classe', 2);
            $table->text('descricao');
            $table->text('periodo');
            $table->boolean('ativo');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('classificacao_rescisao');
    }
}
