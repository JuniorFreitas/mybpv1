<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABELAS = [
        'documentos_curriculos_cat_adm_empresa',
        'documentos_curriculos_adm_empresa',
    ];

    public function up(): void
    {
        if (!Schema::hasTable('clientes')) {
            return;
        }

        foreach (self::TABELAS as $tabela) {
            if (!Schema::hasTable($tabela)) {
                continue;
            }

            $orfao = DB::table($tabela)
                ->leftJoin('clientes', 'clientes.id', '=', $tabela . '.empresa_id')
                ->whereNull('clientes.id')
                ->value($tabela . '.empresa_id');

            if ($orfao !== null) {
                throw new RuntimeException(
                    "A tabela {$tabela} possui empresa_id {$orfao} sem cliente correspondente. Corrija os dados antes da migration."
                );
            }

            Schema::table($tabela, function (Blueprint $table) {
                $table->dropForeign(['empresa_id']);
                $table->foreign('empresa_id')->references('id')->on('clientes')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (array_reverse(self::TABELAS) as $tabela) {
            if (!Schema::hasTable($tabela) || !Schema::hasTable('users')) {
                continue;
            }

            Schema::table($tabela, function (Blueprint $table) {
                $table->dropForeign(['empresa_id']);
                $table->foreign('empresa_id')->references('id')->on('users')->cascadeOnDelete();
            });
        }
    }
};
