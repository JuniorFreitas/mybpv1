<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Unique real em membros_tarefa (P2) — remove duplicatas antes do índice.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Sem PK na pivot: recria pares únicos
        $duplicados = DB::table('membros_tarefa')
            ->select('tarefa_id', 'user_id', DB::raw('COUNT(*) as total'))
            ->groupBy('tarefa_id', 'user_id')
            ->having('total', '>', 1)
            ->get();

        foreach ($duplicados as $dup) {
            DB::table('membros_tarefa')
                ->where('tarefa_id', $dup->tarefa_id)
                ->where('user_id', $dup->user_id)
                ->delete();

            DB::table('membros_tarefa')->insert([
                'tarefa_id' => $dup->tarefa_id,
                'user_id' => $dup->user_id,
            ]);
        }

        Schema::table('membros_tarefa', function (Blueprint $table) {
            // Índice composto antigo (não-unique) da migration de performance
            try {
                $table->dropIndex('idx_wr_membros_tarefa');
            } catch (\Throwable $e) {
                // índice pode não existir em ambientes sem a migration anterior
            }

            $table->unique(['tarefa_id', 'user_id'], 'idx_wr_membros_tarefa_unique');
        });
    }

    public function down(): void
    {
        Schema::table('membros_tarefa', function (Blueprint $table) {
            $table->dropUnique('idx_wr_membros_tarefa_unique');
            $table->index(['tarefa_id', 'user_id'], 'idx_wr_membros_tarefa');
        });
    }
};
