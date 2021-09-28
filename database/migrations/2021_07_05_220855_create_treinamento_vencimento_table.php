<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTreinamentoVencimentoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('treinamento_vencimento', function (Blueprint $table) {
            $table->unsignedBigInteger('treinamento_id')->index('treinamento_vencimento_treinamento_id_foreign');
            $table->unsignedBigInteger('vencimento_id')->index('treinamento_vencimento_vencimento_id_foreign');
            $table->date('data_vencimento');
            $table->date('data_treinamento')->nullable();
            $table->string('numero_fat')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('treinamento_vencimento');
    }
}
