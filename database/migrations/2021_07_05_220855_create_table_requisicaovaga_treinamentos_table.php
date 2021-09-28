<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableRequisicaovagaTreinamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('table_requisicaovaga_treinamentos', function (Blueprint $table) {
            $table->unsignedBigInteger('requisicao_vaga_id')->index('table_requisicaovaga_treinamentos_requisicao_vaga_id_foreign');
            $table->unsignedBigInteger('treinamento_id')->index('table_requisicaovaga_treinamentos_treinamento_id_foreign');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('table_requisicaovaga_treinamentos');
    }
}
