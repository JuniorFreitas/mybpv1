<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Versões do PCMSO por programa (`pcmsos` existente = programa/contrato, ids preservados)
 * e exames complementares por cargo (vaga_id) em cada versão.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pcmsos_versoes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pcmso_id');
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedInteger('versao');
            $table->string('status', 15);
            $table->date('vigencia_inicio')->nullable();
            $table->date('vigencia_fim')->nullable();
            $table->unsignedBigInteger('saude_medico_id')->nullable();
            $table->string('observacao', 1000)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->dateTime('publicado_em')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'pcmso_id', 'status']);
        });

        Schema::create('pcmsos_versoes_exames', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pcmso_versao_id');
            $table->unsignedBigInteger('vaga_id');
            $table->unsignedBigInteger('exame_id');
            $table->json('exame_tipo_ids');
            $table->boolean('obrigatorio')->default(true);
            $table->string('unidade', 10)->nullable();
            $table->unsignedInteger('valor')->nullable();
            $table->string('origem', 10)->default('manual');
            $table->string('observacao', 500)->nullable();
            $table->timestamps();

            $table->unique(['pcmso_versao_id', 'vaga_id', 'exame_id'], 'pcmsos_versoes_exames_unico');
            $table->index(['pcmso_versao_id', 'vaga_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pcmsos_versoes_exames');
        Schema::dropIfExists('pcmsos_versoes');
    }
};
