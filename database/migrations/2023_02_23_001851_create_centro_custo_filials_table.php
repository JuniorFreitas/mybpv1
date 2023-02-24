<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCentroCustoFilialsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('centro_custo_filials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('centro_custo_id');
            $table->unsignedBigInteger('cliente_filial_id');
            $table->boolean('ativo')->default(true);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('clientes')->cascadeOnDelete();
            $table->foreign('centro_custo_id')->references('id')->on('centro_custos')->cascadeOnDelete();
            $table->foreign('cliente_filial_id')->references('id')->on('cliente_filials')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('centro_custo_filials');
    }
}
