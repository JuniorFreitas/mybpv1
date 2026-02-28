<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSegmentosTreinamentoTable extends Migration
{
    /**
     * Run the migrations.
     * Segmentos de treinamento (ALUMAR, VALE, Hidro, etc.) para carteira e vencimentos por padrão.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('segmentos_treinamento', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('slug', 50)->unique();
            $table->boolean('ativo')->default(true);
            $table->json('config_carteira')->nullable()->comment('cabecalho_img, verso_img, exibir_etiqueta_bloqueio');
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
        Schema::dropIfExists('segmentos_treinamento');
    }
}
