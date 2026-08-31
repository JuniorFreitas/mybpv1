<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mesmo padrão de 2026_08_30_110000/110100, agora pra empresa_exames
     * (catálogo de tipos de exame, referenciado por
     * exame_funcionarios.empresa_exame_id).
     */
    public function up(): void
    {
        $pares = DB::table('exame_funcionarios')
            ->select('empresa_exame_id', 'empresa_id')
            ->whereNotNull('empresa_exame_id')
            ->distinct()
            ->get();

        foreach ($pares as $par) {
            $original = DB::table('empresa_exames')->find($par->empresa_exame_id);

            if (!$original || (int) $original->empresa_id === (int) $par->empresa_id) {
                continue;
            }

            $daFilial = DB::table('empresa_exames')
                ->where('empresa_id', $par->empresa_id)
                ->where('nome', $original->nome)
                ->first();

            if (!$daFilial) {
                $novoId = DB::table('empresa_exames')->insertGetId([
                    'user_id' => $original->user_id,
                    'empresa_id' => $par->empresa_id,
                    'nome' => $original->nome,
                    'dados' => $original->dados,
                    'ativo' => $original->ativo,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $novoId = $daFilial->id;
            }

            DB::table('exame_funcionarios')
                ->where('empresa_id', $par->empresa_id)
                ->where('empresa_exame_id', $par->empresa_exame_id)
                ->update(['empresa_exame_id' => $novoId]);
        }
    }

    public function down(): void
    {
        // Não reversível de forma segura.
    }
};
