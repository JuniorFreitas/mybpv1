<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * As 4 concessões de acesso à 2ª empresa feitas na migration 100012
     * (Esmeraldina, Fernando, Emanuelle, Keilyane) não têm papel_id definido
     * — ao contrário da Gleidilene (100016), as contas duplicadas antigas
     * dessas pessoas na 2ª empresa eram Funcionario/Candidato/Fornecedor,
     * sem Papel/habilidades formal pra restaurar. Sem um papel correto, a
     * pessoa mantém o papel da empresa de origem ao trocar — permissão
     * potencialmente errada. Revoga (ativo=false, não deleta) até alguém do
     * negócio decidir o papel certo de cada uma na 2ª empresa.
     */
    public function up(): void
    {
        DB::table('user_empresas')
            ->whereIn('user_id', [39830, 40292, 95713, 79661])
            ->whereNull('papel_id')
            ->update(['ativo' => false]);
    }

    public function down(): void
    {
        DB::table('user_empresas')
            ->whereIn('user_id', [39830, 40292, 95713, 79661])
            ->whereNull('papel_id')
            ->update(['ativo' => true]);
    }
};
