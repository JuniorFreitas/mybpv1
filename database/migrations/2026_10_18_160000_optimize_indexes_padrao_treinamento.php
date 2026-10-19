<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * DBA-PT-003 / DBA-PT-005: remove índices empresa_id redundantes e adiciona
 * composto em carteira_assinaturas para resolverAssinaturaCarteira.
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->dropIndexIfExists('padroes_treinamento', 'padroes_treinamento_empresa_id_index');
        $this->dropIndexIfExists('carteiras_treinamento', 'carteiras_treinamento_empresa_id_index');
        $this->dropIndexIfExists('etiquetas_bloqueio', 'etiquetas_bloqueio_empresa_id_index');

        if (
            Schema::hasTable('carteira_assinaturas')
            && !$this->indexExists('carteira_assinaturas', 'idx_ca_empresa_tipo_padrao_ativo')
        ) {
            Schema::table('carteira_assinaturas', function (Blueprint $table) {
                $table->index(
                    ['empresa_id', 'tipo', 'padrao_treinamento_id', 'ativo'],
                    'idx_ca_empresa_tipo_padrao_ativo'
                );
            });
        }
    }

    public function down(): void
    {
        $this->dropIndexIfExists('carteira_assinaturas', 'idx_ca_empresa_tipo_padrao_ativo');

        $this->addIndexIfMissing('padroes_treinamento', 'padroes_treinamento_empresa_id_index', ['empresa_id']);
        $this->addIndexIfMissing('carteiras_treinamento', 'carteiras_treinamento_empresa_id_index', ['empresa_id']);
        $this->addIndexIfMissing('etiquetas_bloqueio', 'etiquetas_bloqueio_empresa_id_index', ['empresa_id']);
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
