<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $centros = DB::table('centro_custos')
            ->whereNotNull('gestor_id')
            ->select(['id', 'gestor_id', 'empresa_id'])
            ->get();

        $now = now();

        foreach ($centros as $centro) {
            $exists = DB::table('centro_custo_gestores')
                ->where('centro_custo_id', $centro->id)
                ->where('tipo', 'GESTOR_PRINCIPAL')
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('centro_custo_gestores')->insert([
                'centro_custo_id' => $centro->id,
                'usuario_id' => $centro->gestor_id,
                'tipo' => 'GESTOR_PRINCIPAL',
                'ativo' => true,
                'empresa_id' => $centro->empresa_id,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('centro_custo_gestores')
            ->where('tipo', 'GESTOR_PRINCIPAL')
            ->delete();
    }
};
