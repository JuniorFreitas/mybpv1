<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Trilha de evidências das telas públicas (documentos de pré-admissão, carta oferta):
     * quem fez o quê, quando, de onde e com qual aceite. Somente inserção.
     */
    public function up(): void
    {
        if (Schema::hasTable('acessos_publicos_auditorias')) {
            return;
        }

        Schema::create('acessos_publicos_auditorias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('contexto', 60);
            $table->unsignedBigInteger('registro_id')->nullable();
            $table->unsignedBigInteger('curriculo_id')->nullable();
            $table->string('evento', 60);
            $table->string('protocolo', 40)->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('cpf_mascarado', 20)->nullable();
            $table->string('email_mascarado', 150)->nullable();
            $table->json('dados')->nullable();
            $table->string('hash_anterior', 64)->nullable();
            $table->string('hash', 64);
            $table->timestamp('created_at')->useCurrent();

            $table->index(['contexto', 'registro_id', 'id'], 'apa_contexto_registro_idx');
            $table->index(['empresa_id', 'curriculo_id'], 'apa_empresa_curriculo_idx');
            $table->index('protocolo', 'apa_protocolo_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acessos_publicos_auditorias');
    }
};
