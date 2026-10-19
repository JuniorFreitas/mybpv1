<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DBA-206: idx_ca_empresa_tipo_padrao_ativo já começa por empresa_id —
 * remove o índice FK simples para o optimizer usar o composto em mapaAssinaturasCarteira.
 */
return new class extends Migration
{
    public function up(): void
    {
        if ($this->indexExists('carteira_assinaturas', 'idx_ca_empresa_tipo_padrao_ativo')) {
            $this->dropIndexIfExists('carteira_assinaturas', 'carteira_assinaturas_empresa_id_foreign');
        }
    }

    public function down(): void
    {
        $this->addIndexIfMissing(
            'carteira_assinaturas',
            'carteira_assinaturas_empresa_id_foreign',
            ['empresa_id']
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
