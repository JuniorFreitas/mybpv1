<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ASO emitido (NR-7 7.5.19) para um resultado de exame (examesesmts, 1–1).
 * Guarda a "foto" do documento na emissão: médico examinador, coordenador, função, riscos e exames,
 * para o PDF não mudar se o cadastro mudar depois. Assinatura digital vinculada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('examesesmts_aso', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('examesesmt_id')->unique();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('saude_medico_id')->nullable();
            $table->json('examinador');
            $table->json('coordenador')->nullable();
            $table->string('funcao', 150)->nullable();
            $table->json('riscos');
            $table->json('exames');
            $table->unsignedBigInteger('documento_assinatura_id')->nullable();
            $table->unsignedBigInteger('emitido_por')->nullable();
            $table->dateTime('emitido_em');
            $table->timestamps();

            $table->index('empresa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('examesesmts_aso');
    }
};
