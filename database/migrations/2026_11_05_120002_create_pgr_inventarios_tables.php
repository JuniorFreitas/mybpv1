<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * PGR básico (NR-1): inventário de riscos por cargo, em versões.
 * pgr_inventarios → pgr_inventarios_cargos (vaga_id) → pgr_inventarios_riscos (exame_riscos).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pgr_inventarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedInteger('versao');
            $table->string('status', 15);
            $table->date('vigencia_inicio')->nullable();
            $table->date('vigencia_fim')->nullable();
            $table->string('responsavel_tecnico', 150)->nullable();
            $table->string('responsavel_registro', 60)->nullable();
            $table->string('observacao', 1000)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->dateTime('publicado_em')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'status']);
        });

        Schema::create('pgr_inventarios_cargos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pgr_inventario_id');
            $table->unsignedBigInteger('vaga_id');
            $table->boolean('ausencia_risco')->default(false);
            $table->string('observacao', 500)->nullable();
            $table->timestamps();

            $table->unique(['pgr_inventario_id', 'vaga_id']);
            $table->index('vaga_id');
        });

        Schema::create('pgr_inventarios_riscos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pgr_inventario_cargo_id');
            $table->unsignedBigInteger('exame_risco_id');
            $table->string('fonte_geradora', 200)->nullable();
            $table->string('intensidade', 100)->nullable();
            $table->string('medidas_controle', 1000)->nullable();
            $table->timestamps();

            $table->unique(['pgr_inventario_cargo_id', 'exame_risco_id'], 'pgr_inv_riscos_cargo_risco_unique');
            $table->index('exame_risco_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pgr_inventarios_riscos');
        Schema::dropIfExists('pgr_inventarios_cargos');
        Schema::dropIfExists('pgr_inventarios');
    }
};
