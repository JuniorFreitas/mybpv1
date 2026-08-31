<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mesmo padrão das migrations 110000/110100/110200, agora pra pcmsos
     * (catálogo, referenciado por exame_funcionarios.pcmso_id). Tabela
     * simples, sem filhos — duplicar é seguro.
     */
    public function up(): void
    {
        $pares = DB::table('exame_funcionarios')
            ->select('pcmso_id', 'empresa_id')
            ->whereNotNull('pcmso_id')
            ->distinct()
            ->get();

        foreach ($pares as $par) {
            $original = DB::table('pcmsos')->find($par->pcmso_id);

            if (!$original || (int) $original->empresa_id === (int) $par->empresa_id) {
                continue;
            }

            $daFilial = DB::table('pcmsos')
                ->where('empresa_id', $par->empresa_id)
                ->where('label', $original->label)
                ->first();

            if (!$daFilial) {
                $novoId = DB::table('pcmsos')->insertGetId([
                    'empresa_id' => $par->empresa_id,
                    'label' => $original->label,
                    'ativo' => $original->ativo,
                ]);
            } else {
                $novoId = $daFilial->id;
            }

            DB::table('exame_funcionarios')
                ->where('empresa_id', $par->empresa_id)
                ->where('pcmso_id', $par->pcmso_id)
                ->update(['pcmso_id' => $novoId]);
        }
    }

    public function down(): void
    {
        // Não reversível de forma segura.
    }
};
