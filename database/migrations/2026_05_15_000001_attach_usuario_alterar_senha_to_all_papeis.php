<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Garante a habilidade usuario_alterar-senha em todos os papéis existentes.
     */
    public function up(): void
    {
        $habilidadeId = DB::table('habilidades')->where('nome', 'usuario_alterar-senha')->value('id');
        if (!$habilidadeId) {
            return;
        }

        $papelIds = DB::table('papeis')->pluck('id');
        foreach ($papelIds as $papelId) {
            $exists = DB::table('papeis_habilidades')
                ->where('papel_id', $papelId)
                ->where('habilidade_id', $habilidadeId)
                ->exists();
            if (!$exists) {
                DB::table('papeis_habilidades')->insert([
                    'papel_id' => $papelId,
                    'habilidade_id' => $habilidadeId,
                ]);
            }
        }
    }

    /**
     * Não remove vínculos em down (evita perda acidental em rollback).
     */
    public function down(): void
    {
    }
};
