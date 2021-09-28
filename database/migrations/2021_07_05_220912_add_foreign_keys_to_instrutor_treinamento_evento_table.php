<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToInstrutorTreinamentoEventoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instrutor_treinamento_evento', function (Blueprint $table) {
            $table->foreign('instrutor_id')->references('id')->on('instrutores')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('treinamento_evento_id')->references('id')->on('treinamento_eventos')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instrutor_treinamento_evento', function (Blueprint $table) {
            $table->dropForeign('instrutor_treinamento_evento_instrutor_id_foreign');
            $table->dropForeign('instrutor_treinamento_evento_treinamento_evento_id_foreign');
        });
    }
}
