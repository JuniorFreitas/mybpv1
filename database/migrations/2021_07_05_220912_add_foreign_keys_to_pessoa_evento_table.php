<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToPessoaEventoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pessoa_evento', function (Blueprint $table) {
            $table->foreign('pessoa_treinamento_id')->references('id')->on('pessoas_empresas')->onUpdate('RESTRICT')->onDelete('CASCADE');
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
        Schema::table('pessoa_evento', function (Blueprint $table) {
            $table->dropForeign('pessoa_evento_pessoa_treinamento_id_foreign');
            $table->dropForeign('pessoa_evento_treinamento_evento_id_foreign');
        });
    }
}
