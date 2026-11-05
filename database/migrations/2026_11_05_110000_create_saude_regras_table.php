<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Regras da empresa (Saúde Ocupacional): "QUANDO condições ENTÃO periodicidade".
 * Condições e tipos de exame em JSON validado pelo domínio (CatalogoCondicoes).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saude_regras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nome', 120);
            $table->string('modelo', 40)->nullable();
            $table->json('exame_tipo_ids');
            $table->json('condicoes');
            $table->string('unidade', 10);
            $table->unsignedInteger('valor');
            $table->boolean('ativo')->default(true);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saude_regras');
    }
};
