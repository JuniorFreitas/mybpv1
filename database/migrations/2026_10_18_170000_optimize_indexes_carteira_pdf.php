<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DBA carteiraPdf:
 * - curriculo_telefone (curriculo_id, principal) — prefetch telefone principal
 * - cliente_area_etiquetas (cliente_id, area_etiqueta_id) — supervisor por área
 * - remove índices duplicados em treinamentos.feedback_id e treinamento_vencimento.vencimento_id
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing(
            'curriculo_telefone',
            'idx_ct_curriculo_principal',
            ['curriculo_id', 'principal']
        );
        // Composto cobre a FK curriculo_id — remove índice simples para o optimizer usar o composto
        $this->dropIndexIfExists('curriculo_telefone', 'curriculo_telefone_curriculo_id_foreign');

        $this->addIndexIfMissing(
            'cliente_area_etiquetas',
            'idx_cae_cliente_area',
            ['cliente_id', 'area_etiqueta_id']
        );
        $this->dropIndexIfExists('cliente_area_etiquetas', 'cliente_area_etiquetas_cliente_id_foreign');

        $this->dropIndexIfExists('treinamentos', 'idx_feedback_id_optimized');
        $this->dropIndexIfExists('treinamento_vencimento', 'idx_vencimento_id_optimized');
    }

    public function down(): void
    {
        $this->addIndexIfMissing(
            'curriculo_telefone',
            'curriculo_telefone_curriculo_id_foreign',
            ['curriculo_id']
        );
        $this->dropIndexIfExists('curriculo_telefone', 'idx_ct_curriculo_principal');

        $this->addIndexIfMissing(
            'cliente_area_etiquetas',
            'cliente_area_etiquetas_cliente_id_foreign',
            ['cliente_id']
        );
        $this->dropIndexIfExists('cliente_area_etiquetas', 'idx_cae_cliente_area');

        $this->addIndexIfMissing('treinamentos', 'idx_feedback_id_optimized', ['feedback_id']);
        $this->addIndexIfMissing('treinamento_vencimento', 'idx_vencimento_id_optimized', ['vencimento_id']);
    }

    private function indexExists(string $table, string $indexName): bool
    {
        if (!Schema::hasTable($table)) {
            return false;
        }

        $rows = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);

        return count($rows) > 0;
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!$this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($indexName) {
            $tableBlueprint->dropIndex($indexName);
        });
    }

    /**
     * @param  list<string>  $columns
     */
    private function addIndexIfMissing(string $table, string $indexName, array $columns): void
    {
        if (!Schema::hasTable($table) || $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $tableBlueprint) use ($columns, $indexName) {
            $tableBlueprint->index($columns, $indexName);
        });
    }
};
