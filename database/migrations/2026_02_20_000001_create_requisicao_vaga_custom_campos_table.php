<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequisicaoVagaCustomCamposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requisicao_vaga_custom_campos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('empresa_id')->index();
            $table->string('label');
            $table->string('tipo', 20); // sim_nao, texto, select
            $table->json('opcoes')->nullable(); // para select: ["Opção A", "Opção B"]
            $table->boolean('obrigatorio')->default(false);
            $table->unsignedInteger('ordem')->default(0);
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('clientes')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('requisicao_vaga_custom_campos');
    }
}
