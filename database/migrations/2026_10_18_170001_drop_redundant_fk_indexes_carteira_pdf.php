<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Complemento 170000: em ambientes que já rodaram a migration sem dropar
 * os índices FK simples (cobertos pelos compostos), remove a redundância.
 */
return new class extends Migration
{
    public function up(): void
    {
        if ($this->indexExists('curriculo_telefone', 'idx_ct_curriculo_principal')) {
            $this->dropIndexIfExists('curriculo_telefone', 'curriculo_telefone_curriculo_id_foreign');
        }

        if ($this->indexExists('cliente_area_etiquetas', 'idx_cae_cliente_area')) {
            $this->dropIndexIfExists('cliente_area_etiquetas', 'cliente_area_etiquetas_cliente_id_foreign');
        }
    }

    public function down(): void
    {
        $this->addIndexIfMissing(
            'curriculo_telefone',
            'curriculo_telefone_curriculo_id_foreign',
            ['curriculo_id']
        );
        $this->addIndexIfMissing(
            'cliente_area_etiquetas',
            'cliente_area_etiquetas_cliente_id_foreign',
            ['cliente_id']
        );
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
