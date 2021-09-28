<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTreinamentoEventosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('treinamento_eventos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id')->index('treinamento_eventos_cliente_id_foreign');
            $table->unsignedBigInteger('treinamento_sgi_id')->index('treinamento_eventos_treinamento_sgi_id_foreign');
            $table->unsignedBigInteger('empresa_treinamento_id')->index('treinamento_eventos_empresa_treinamento_id_foreign');
            $table->dateTime('data_inicio');
            $table->dateTime('data_fim');
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
        Schema::dropIfExists('treinamento_eventos');
    }
}
