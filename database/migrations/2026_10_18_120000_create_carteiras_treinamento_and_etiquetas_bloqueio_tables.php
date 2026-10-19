<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modelos de Carteira e Etiqueta de bloqueio (global empresa_id null ou custom por tenant).
 * Spec: docs/PADRAO_TREINAMENTO.md
 */
class CreateCarteirasTreinamentoAndEtiquetasBloqueioTables extends Migration
{
    public function up()
    {
        Schema::create('carteiras_treinamento', function (Blueprint $table) {
            $table->id();
            // Sem index isolado em empresa_id: coberto pelo unique (empresa_id, slug)
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome');
            $table->string('slug', 50);
            $table->boolean('ativo')->default(true);
            $table->string('cabecalho_img')->nullable();
            $table->string('verso_img')->nullable();
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->unique(['empresa_id', 'slug'], 'carteiras_treinamento_empresa_slug_unique');
        });

        Schema::create('etiquetas_bloqueio', function (Blueprint $table) {
            $table->id();
            // Sem index isolado em empresa_id: coberto pelo unique (empresa_id, slug)
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->string('nome');
            $table->string('slug', 50);
            $table->boolean('ativo')->default(true);
            $table->string('ramal_emergencia')->nullable();
            $table->string('bloqueio_texto_nao_use')->nullable();
            $table->string('bloqueio_texto_demissao')->nullable();
            $table->string('bloqueio_texto_cuidado')->nullable();
            $table->string('bloqueio_texto_homens_trabalhando')->nullable();
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->unique(['empresa_id', 'slug'], 'etiquetas_bloqueio_empresa_slug_unique');
        });
    }

    public function down()
    {
        Schema::dropIfExists('etiquetas_bloqueio');
        Schema::dropIfExists('carteiras_treinamento');
    }
}
