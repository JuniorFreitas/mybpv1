<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vínculo do médico com a empresa: papel (coordenador do PCMSO | examinador), clínica e vigência.
 * Um coordenador vigente por vez (validado no domínio).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saude_medicos_vinculos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('saude_medico_id');
            $table->unsignedBigInteger('empresa_id');
            $table->string('papel', 20);
            $table->unsignedBigInteger('empresa_exame_id')->nullable();
            $table->date('vigencia_inicio');
            $table->date('vigencia_fim')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'papel', 'vigencia_inicio']);
            $table->index('saude_medico_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saude_medicos_vinculos');
    }
};
