<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Completa os tipos de risco do PGR (NR-1): Acidentes e Psicossocial.
 * Idempotente; down só remove se nenhum risco usar o tipo.
 */
return new class extends Migration
{
    private const TIPOS = ['Acidentes', 'Psicossocial'];

    public function up(): void
    {
        foreach (self::TIPOS as $label) {
            if (!DB::table('exame_risco_tipos')->where('label', $label)->exists()) {
                DB::table('exame_risco_tipos')->insert([
                    'label' => $label,
                    'ativo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        $ids = DB::table('exame_risco_tipos')->whereIn('label', self::TIPOS)->pluck('id');
        $emUso = DB::table('exame_riscos')->whereIn('risco_tipo', $ids)->pluck('risco_tipo')->unique();

        DB::table('exame_risco_tipos')->whereIn('id', $ids->diff($emUso))->delete();
    }
};
