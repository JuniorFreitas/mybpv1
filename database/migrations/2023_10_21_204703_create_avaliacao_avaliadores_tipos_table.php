<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvaliacaoAvaliadoresTiposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avaliacao_avaliadores_tipos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('label');
            $table->string('descricao')->nullable();
            $table->boolean('ativo')->default(true);

            $table->foreign('empresa_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['empresa_id', 'label'], 'avaliacao_avaliadores_tipos_empresa_id_label_unique');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avaliacao_avaliadores_tipos');
    }
}
