<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAvaliacoesTopicosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('avaliacoes_topicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedBigInteger('avaliacao_tipo_id')->nullable();
            $table->foreign('avaliacao_tipo_id')->references('id')->on('avaliacoes_tipos')->cascadeOnDelete();
            $table->unsignedBigInteger('topico_pai_id')->nullable();
            $table->foreign('topico_pai_id')->references('id')->on('avaliacoes_topicos')->cascadeOnDelete();
            $table->string('topico');
            $table->text('topico_explicacao')->nullable();
            $table->boolean('ativo')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('avaliacoes_topicos');
    }
}
