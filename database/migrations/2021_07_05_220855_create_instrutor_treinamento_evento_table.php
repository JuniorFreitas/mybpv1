<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstrutorTreinamentoEventoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('instrutor_treinamento_evento', function (Blueprint $table) {
            $table->unsignedBigInteger('instrutor_id')->index('instrutor_treinamento_evento_instrutor_id_foreign');
            $table->unsignedBigInteger('treinamento_evento_id')->index('instrutor_treinamento_evento_treinamento_evento_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('instrutor_treinamento_evento');
    }
}
