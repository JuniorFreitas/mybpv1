<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Cada empresa existente vira grupo de 1, matriz=true — zero mudança de
     * comportamento visível. Agrupar empresas de fato num mesmo grupo é uma
     * ação manual futura (tela de administração), não parte desta migration.
     * Idempotente: só processa clientes com grupo_id ainda nulo.
     */
    public function up(): void
    {
        $empresas = DB::table('clientes')
            ->whereNull('grupo_id')
            ->select('id', 'nome_fantasia', 'nome', 'razao_social')
            ->get();

        foreach ($empresas as $empresa) {
            $nomeGrupo = $empresa->nome_fantasia ?: ($empresa->nome ?: ($empresa->razao_social ?: "Grupo #{$empresa->id}"));

            $grupoId = DB::table('grupos')->insertGetId([
                'nome' => $nomeGrupo,
                'ativo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('clientes')
                ->where('id', $empresa->id)
                ->update([
                    'grupo_id' => $grupoId,
                    'matriz' => true,
                ]);
        }
    }

    public function down(): void
    {
        // Não reversível de forma útil (perderia o mapeamento grupo<->empresa
        // se grupos já tiverem sido reorganizados manualmente após rodar).
    }
};
