<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToTableRequisicaovagaTreinamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('table_requisicaovaga_treinamentos', function (Blueprint $table) {
            $table->foreign('requisicao_vaga_id')->references('id')->on('requisicao_vagas')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('treinamento_id')->references('id')->on('treinamentos')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('table_requisicaovaga_treinamentos', function (Blueprint $table) {
            $table->dropForeign('table_requisicaovaga_treinamentos_requisicao_vaga_id_foreign');
            $table->dropForeign('table_requisicaovaga_treinamentos_treinamento_id_foreign');
        });
    }
}
