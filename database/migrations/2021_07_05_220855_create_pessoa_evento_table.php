<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePessoaEventoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pessoa_evento', function (Blueprint $table) {
            $table->unsignedBigInteger('pessoa_treinamento_id')->index('pessoa_evento_pessoa_treinamento_id_foreign');
            $table->unsignedBigInteger('treinamento_evento_id')->index('pessoa_evento_treinamento_evento_id_foreign');
            $table->integer('nota')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pessoa_evento');
    }
}
