<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('habilidades', function (Blueprint $table) {
            if (!Schema::hasColumn('habilidades', 'modulo')) {
                $table->string('modulo', 80)->nullable()->after('descricao');
            }
            if (!Schema::hasColumn('habilidades', 'recurso')) {
                $table->string('recurso', 120)->nullable()->after('modulo');
            }
            if (!Schema::hasColumn('habilidades', 'acao')) {
                $table->string('acao', 80)->nullable()->after('recurso');
            }
        });

        $duplicados = (int) DB::table('habilidades')
            ->select('nome')
            ->groupBy('nome')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        if ($duplicados === 0) {
            try {
                Schema::table('habilidades', function (Blueprint $table) {
                    $table->unique('nome', 'habilidades_nome_unique');
                });
            } catch (\Throwable) {
                // Índice já existe ou engine não suportou — segue sem unique.
            }
        }

        try {
            Schema::table('habilidades', function (Blueprint $table) {
                $table->index('modulo', 'habilidades_modulo_index');
            });
        } catch (\Throwable) {
            // Índice já existe.
        }
    }

    public function down(): void
    {
        try {
            Schema::table('habilidades', function (Blueprint $table) {
                $table->dropUnique('habilidades_nome_unique');
            });
        } catch (\Throwable) {
        }

        try {
            Schema::table('habilidades', function (Blueprint $table) {
                $table->dropIndex('habilidades_modulo_index');
            });
        } catch (\Throwable) {
        }

        Schema::table('habilidades', function (Blueprint $table) {
            $cols = [];
            foreach (['acao', 'recurso', 'modulo'] as $col) {
                if (Schema::hasColumn('habilidades', $col)) {
                    $cols[] = $col;
                }
            }
            if ($cols !== []) {
                $table->dropColumn($cols);
            }
        });
    }
};
