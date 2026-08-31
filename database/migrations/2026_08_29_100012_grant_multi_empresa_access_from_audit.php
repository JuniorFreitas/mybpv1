<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Concede acesso à 2ª empresa para as pessoas que a auditoria da Fase 1
     * flagou com contas duplicadas em empresas diferentes (Categoria B —
     * mesma pessoa, sem suporte a multi-empresa até agora). A conta "keeper"
     * de cada uma (a que manteve o login original) ganha 1 linha extra em
     * user_empresas para a outra empresa onde ela também atuava.
     *
     * Não inclui bp03@bpse.com.br (Marta) nem gestao.pcs@bpse.com.br (Brunna)
     * — nesses dois casos as contas duplicadas eram na MESMA empresa (BPSE),
     * não em empresas diferentes; não é um caso de multi-empresa.
     */
    public function up(): void
    {
        $concessoes = [
            // Gleidilene — keeper Gestor 40283 na BPSE(104), também atuava na CMPC(40568)
            ['user_id' => 40283, 'empresa_id' => 40568],
            // Emanuelle — keeper Administrador 95713 na BP Academy(95477), também na BPSE(104)
            ['user_id' => 95713, 'empresa_id' => 104],
            // Esmeraldina — keeper Administrador 39830 na Pillar(39765), também na CMPC(40568)
            ['user_id' => 39830, 'empresa_id' => 40568],
            // Fernando Pereira dos Santos — keeper Gestor 40292 na Pillar(39765), também na CMPC(40568)
            ['user_id' => 40292, 'empresa_id' => 40568],
            // Keilyane — keeper Administrador 79661 na MONTISOL(63122), também na BPSE(104)
            ['user_id' => 79661, 'empresa_id' => 104],
        ];

        $agora = now();
        DB::table('user_empresas')->insertOrIgnore(
            array_map(fn ($c) => $c + ['ativo' => true, 'created_at' => $agora, 'updated_at' => $agora], $concessoes)
        );
    }

    public function down(): void
    {
        DB::table('user_empresas')->whereIn('user_id', [40283, 95713, 39830, 40292, 79661])
            ->whereIn('empresa_id', [40568, 104, 63122])
            ->delete();
    }
};
