<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Exames sugeridos por risco (ex.: Ruído → Audiometria em Admissional/Periódico/Demissional).
 * Usado para sugerir exames por cargo no PCMSO a partir do PGR vigente.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exame_riscos_exames', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('exame_risco_id');
            $table->unsignedBigInteger('exame_id');
            $table->json('exame_tipo_ids');
            $table->timestamps();

            $table->unique(['exame_risco_id', 'exame_id']);
            $table->index('exame_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exame_riscos_exames');
    }
};
