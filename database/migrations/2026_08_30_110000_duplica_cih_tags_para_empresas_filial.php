<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * cih_tags é catálogo por empresa (mesma decisão de Centro de Custo —
     * não compartilhado no grupo). Os registros históricos de cihs
     * reatribuídos pra empresa-filial (migration 2026_08_30_100000)
     * continuaram apontando pra tag_id da MATRIZ — e essas tags também são
     * usadas por registros que continuam sendo da matriz, então não dá pra
     * só mover a tag. Duplica a tag (mesmo label) pra cada empresa-filial
     * que a usa, e reaponta só os cihs dessa filial pra cópia nova.
     */
    public function up(): void
    {
        $pares = DB::table('cihs')
            ->select('tag_id', 'empresa_id')
            ->whereNotNull('tag_id')
            ->distinct()
            ->get();

        foreach ($pares as $par) {
            $tagOriginal = DB::table('cih_tags')->find($par->tag_id);

            if (!$tagOriginal || (int) $tagOriginal->empresa_id === (int) $par->empresa_id) {
                continue; // já é da empresa certa (é a matriz dona da tag) ou tag não existe
            }

            $tagFilial = DB::table('cih_tags')
                ->where('empresa_id', $par->empresa_id)
                ->where('label', $tagOriginal->label)
                ->first();

            if (!$tagFilial) {
                $novoId = DB::table('cih_tags')->insertGetId([
                    'empresa_id' => $par->empresa_id,
                    'label' => $tagOriginal->label,
                    'ativo' => $tagOriginal->ativo,
                    'anexo_obrigatorio' => $tagOriginal->anexo_obrigatorio,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $novoId = $tagFilial->id;
            }

            DB::table('cihs')
                ->where('empresa_id', $par->empresa_id)
                ->where('tag_id', $par->tag_id)
                ->update(['tag_id' => $novoId]);
        }
    }

    public function down(): void
    {
        // Não reversível de forma segura.
    }
};
