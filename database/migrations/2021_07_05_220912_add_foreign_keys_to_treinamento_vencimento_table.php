<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToTreinamentoVencimentoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('treinamento_vencimento', function (Blueprint $table) {
            $table->foreign('treinamento_id')->references('id')->on('treinamentos')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('vencimento_id')->references('id')->on('vencimentos')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('treinamento_vencimento', function (Blueprint $table) {
            $table->dropForeign('treinamento_vencimento_treinamento_id_foreign');
            $table->dropForeign('treinamento_vencimento_vencimento_id_foreign');
        });
    }
}
