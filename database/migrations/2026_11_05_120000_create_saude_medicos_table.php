<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Médicos responsáveis (Saúde Ocupacional): coordenador do PCMSO e examinadores.
 * Unicidade de CRM/UF por empresa validada no domínio (soft delete impede unique no banco).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saude_medicos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('nome', 150);
            $table->string('crm', 10);
            $table->char('crm_uf', 2);
            $table->string('rqe', 20)->nullable();
            $table->string('especialidade', 100)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('telefone', 20)->nullable();
            $table->boolean('ativo')->default(true);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'ativo']);
            $table->index(['empresa_id', 'crm', 'crm_uf']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('saude_medicos');
    }
};
