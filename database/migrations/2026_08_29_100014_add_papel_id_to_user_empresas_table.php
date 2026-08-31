<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Gap encontrado testando a troca de empresa ativa: permissões
     * (habilidades) eram resolvidas só por users.grupo_id — fixo por
     * usuário, não por empresa. Um usuário com Papel diferente em cada
     * empresa (ex: Gestor com Controle de Ponto na BPSE, Administrador sem
     * isso na CMPC) mantinha sempre as habilidades da empresa "principal" ao
     * trocar de empresa ativa. `papel_id` aqui permite 1 papel por vínculo
     * (user, empresa), não 1 papel fixo por usuário.
     */
    public function up(): void
    {
        Schema::table('user_empresas', function (Blueprint $table) {
            $table->unsignedInteger('papel_id')->nullable()->after('empresa_id');
            $table->foreign('papel_id')->references('id')->on('papeis')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('user_empresas', function (Blueprint $table) {
            $table->dropForeign(['papel_id']);
            $table->dropColumn('papel_id');
        });
    }
};
