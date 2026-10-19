<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Padrão de treinamento por empresa + pivots de lotação (Matriz/Filial) e vencimentos.
 * Spec: docs/PADRAO_TREINAMENTO.md
 */
class CreatePadroesTreinamentoTables extends Migration
{
    public function up()
    {
        Schema::create('padroes_treinamento', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->index();
            $table->string('nome');
            $table->string('slug', 50);
            $table->boolean('ativo')->default(true);
            $table->boolean('tem_carteira')->default(false);
            $table->unsignedBigInteger('carteira_treinamento_id')->nullable();
            $table->boolean('tem_etiqueta_bloqueio')->default(false);
            $table->unsignedBigInteger('etiqueta_bloqueio_id')->nullable();
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('clientes')->onDelete('cascade');
            $table->foreign('carteira_treinamento_id')->references('id')->on('carteiras_treinamento')->onDelete('set null');
            $table->foreign('etiqueta_bloqueio_id')->references('id')->on('etiquetas_bloqueio')->onDelete('set null');
            $table->unique(['empresa_id', 'slug'], 'padroes_treinamento_empresa_slug_unique');
            $table->index(['empresa_id', 'ativo'], 'padroes_treinamento_empresa_ativo_index');
        });

        Schema::create('padrao_treinamento_lotacao', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('padrao_treinamento_id');
            $table->boolean('is_matriz')->default(false);
            $table->unsignedBigInteger('cliente_filial_id')->nullable();
            /** Chave estável para unique MySQL 5.7: "matriz" ou "f:{id}" */
            $table->string('lotacao_key', 32);
            $table->timestamps();

            $table->foreign('padrao_treinamento_id', 'ptl_padrao_fk')
                ->references('id')->on('padroes_treinamento')->onDelete('cascade');
            $table->foreign('cliente_filial_id', 'ptl_filial_fk')
                ->references('id')->on('cliente_filials')->onDelete('cascade');
            $table->unique(['padrao_treinamento_id', 'lotacao_key'], 'ptl_padrao_lotacao_unique');
            $table->index('cliente_filial_id', 'ptl_filial_index');
        });

        Schema::create('padrao_treinamento_vencimento', function (Blueprint $table) {
            $table->unsignedBigInteger('padrao_treinamento_id');
            // vencimentos.id é int unsigned (legado), não bigint
            $table->unsignedInteger('vencimento_id');

            $table->primary(['padrao_treinamento_id', 'vencimento_id'], 'ptv_primary');
            $table->foreign('padrao_treinamento_id', 'ptv_padrao_fk')
                ->references('id')->on('padroes_treinamento')->onDelete('cascade');
            $table->foreign('vencimento_id', 'ptv_vencimento_fk')
                ->references('id')->on('vencimentos')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('padrao_treinamento_vencimento');
        Schema::dropIfExists('padrao_treinamento_lotacao');
        Schema::dropIfExists('padroes_treinamento');
    }
}
