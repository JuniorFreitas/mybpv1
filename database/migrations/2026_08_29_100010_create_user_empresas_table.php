<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fase 3 — pivot dedicada de ACESSO (não confundir com
     * `empresa_funcionarios`, que é um cache de RH derivado de
     * users.empresa_id, sem semântica de login/permissão — ver auditoria).
     * user_id e empresa_id apontam para `users` porque `clientes` (empresa)
     * compartilha PK com `users` (mesmo padrão de centro_custo_gestores).
     */
    public function up(): void
    {
        Schema::create('user_empresas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('empresa_id');
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('empresa_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unique(['user_id', 'empresa_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_empresas');
    }
};
