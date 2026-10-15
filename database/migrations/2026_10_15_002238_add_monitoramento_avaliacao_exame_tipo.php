<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LABEL = 'Monitoramento/Avaliação';

    /**
     * Inclui o tipo de ordem Monitoramento/Avaliação em exame_tipos (global)
     * e nas opções legado "Tipo de ordem" de cada empresa (resposta_alternativas).
     */
    public function up(): void
    {
        if (! Schema::hasTable('exame_tipos')) {
            return;
        }

        $now = now();
        $exameTipoId = DB::table('exame_tipos')->where('label', self::LABEL)->value('id');

        if (! $exameTipoId) {
            $exameTipoId = DB::table('exame_tipos')->insertGetId([
                'label' => self::LABEL,
                'ativo' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('exame_tipos')
                ->where('id', $exameTipoId)
                ->update(['ativo' => true, 'updated_at' => $now]);
        }

        if (! Schema::hasTable('alternativa_formularios') || ! Schema::hasTable('resposta_alternativas')) {
            return;
        }

        $tipoOrdemIds = DB::table('alternativa_formularios')
            ->where('nome', 'Tipo de ordem')
            ->pluck('id');

        foreach ($tipoOrdemIds as $alternativaId) {
            $jaExiste = DB::table('resposta_alternativas')
                ->where('alternativa_id', $alternativaId)
                ->where('label', self::LABEL)
                ->exists();

            if ($jaExiste) {
                continue;
            }

            $proximaOrdem = (int) DB::table('resposta_alternativas')
                ->where('alternativa_id', $alternativaId)
                ->max('ordem');

            DB::table('resposta_alternativas')->insert([
                'alternativa_id' => $alternativaId,
                'label' => self::LABEL,
                'selecionado' => null,
                'link_id' => null,
                'ordem' => $proximaOrdem + 1,
                'value' => $exameTipoId,
            ]);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('resposta_alternativas') && Schema::hasTable('alternativa_formularios')) {
            $tipoOrdemIds = DB::table('alternativa_formularios')
                ->where('nome', 'Tipo de ordem')
                ->pluck('id');

            if ($tipoOrdemIds->isNotEmpty()) {
                DB::table('resposta_alternativas')
                    ->whereIn('alternativa_id', $tipoOrdemIds)
                    ->where('label', self::LABEL)
                    ->delete();
            }
        }

        if (Schema::hasTable('exame_tipos')) {
            DB::table('exame_tipos')->where('label', self::LABEL)->delete();
        }
    }
};
