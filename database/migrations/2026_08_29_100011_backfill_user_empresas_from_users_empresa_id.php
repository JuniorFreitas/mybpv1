<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Dual-write: todo usuário com empresa_id preenchido ganha 1 linha em
     * user_empresas, espelhando o estado atual sem remover users.empresa_id
     * (mantido como fallback/compat — ver decisão da Fase 3). Daqui pra
     * frente, dar acesso a uma empresa extra é só inserir outra linha aqui.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('empresa_id')
            ->orderBy('id')
            ->select('id', 'empresa_id')
            ->chunkById(1000, function ($rows) {
                $agora = now();

                $registros = $rows->map(fn ($row) => [
                    'user_id' => $row->id,
                    'empresa_id' => $row->empresa_id,
                    'ativo' => true,
                    'created_at' => $agora,
                    'updated_at' => $agora,
                ])->all();

                DB::table('user_empresas')->insertOrIgnore($registros);
            });
    }

    public function down(): void
    {
        DB::table('user_empresas')->truncate();
    }
};
