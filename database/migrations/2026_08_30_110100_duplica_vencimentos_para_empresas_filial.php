<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mesmo problema e mesma correção da migration 2026_08_30_110000 (cih_tags),
     * agora pra `vencimentos` (catálogo de vencimento de treinamento,
     * referenciado por treinamento_vencimento_historicos.vencimento_id).
     * Duplica o vencimento (mesmo label/prazo/etc) pra cada empresa-filial
     * que o usa, sem mexer no original da matriz.
     */
    public function up(): void
    {
        $pares = DB::table('treinamento_vencimento_historicos')
            ->select('vencimento_id', 'empresa_id')
            ->whereNotNull('vencimento_id')
            ->distinct()
            ->get();

        foreach ($pares as $par) {
            $original = DB::table('vencimentos')->find($par->vencimento_id);

            if (!$original || (int) $original->empresa_id === (int) $par->empresa_id) {
                continue;
            }

            $daFilial = DB::table('vencimentos')
                ->where('empresa_id', $par->empresa_id)
                ->where('label', $original->label)
                ->first();

            if (!$daFilial) {
                $novoId = DB::table('vencimentos')->insertGetId([
                    'label' => $original->label,
                    'descricao' => $original->descricao,
                    'prazo_parada' => $original->prazo_parada,
                    'prazo_fixo' => $original->prazo_fixo,
                    'ordem' => $original->ordem,
                    'ativo' => $original->ativo,
                    'empresa_id' => $par->empresa_id,
                    'segmento_treinamento_id' => $original->segmento_treinamento_id,
                    'vinculo_todos_cargos' => $original->vinculo_todos_cargos,
                    'label_reduzida' => $original->label_reduzida,
                    'exibir_na_carteira' => $original->exibir_na_carteira,
                ]);
            } else {
                $novoId = $daFilial->id;
            }

            DB::table('treinamento_vencimento_historicos')
                ->where('empresa_id', $par->empresa_id)
                ->where('vencimento_id', $par->vencimento_id)
                ->update(['vencimento_id' => $novoId]);
        }
    }

    public function down(): void
    {
        // Não reversível de forma segura.
    }
};
