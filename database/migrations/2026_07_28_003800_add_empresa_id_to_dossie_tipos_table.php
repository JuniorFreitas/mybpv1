<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddEmpresaIdToDossieTiposTable extends Migration
{
    /**
     * Run the migrations.
     *
     * empresa_id NULL = catálogo global; preenchido = override/exclusivo da empresa.
     * Idempotente: ambientes que já criaram a tabela com empresa_id não reaplicam índices/FK.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('dossie_tipos')) {
            return;
        }

        Schema::table('dossie_tipos', function (Blueprint $table) {
            if (!Schema::hasColumn('dossie_tipos', 'empresa_id')) {
                $table->unsignedBigInteger('empresa_id')->nullable()->after('id');
            }
        });

        $this->dropIndexIfExists('dossie_tipos', 'dossie_tipos_tipo_unique');
        $this->dropIndexIfExists('dossie_tipos', 'dossie_tipos_chave_unique');

        if (!$this->indexExists('dossie_tipos', 'dossie_tipos_empresa_tipo_idx')) {
            Schema::table('dossie_tipos', function (Blueprint $table) {
                $table->index(['empresa_id', 'tipo'], 'dossie_tipos_empresa_tipo_idx');
            });
        }

        if (!$this->indexExists('dossie_tipos', 'dossie_tipos_empresa_chave_idx')) {
            Schema::table('dossie_tipos', function (Blueprint $table) {
                $table->index(['empresa_id', 'chave'], 'dossie_tipos_empresa_chave_idx');
            });
        }

        if (!$this->indexExists('dossie_tipos', 'dossie_tipos_empresa_ativo_ordem_idx')) {
            Schema::table('dossie_tipos', function (Blueprint $table) {
                $table->index(['empresa_id', 'ativo', 'ordem'], 'dossie_tipos_empresa_ativo_ordem_idx');
            });
        }

        if (!$this->foreignKeyExists('dossie_tipos', 'dossie_tipos_empresa_id_foreign')) {
            Schema::table('dossie_tipos', function (Blueprint $table) {
                $table->foreign('empresa_id', 'dossie_tipos_empresa_id_foreign')
                    ->references('id')
                    ->on('clientes')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('dossie_tipos') || !Schema::hasColumn('dossie_tipos', 'empresa_id')) {
            return;
        }

        if ($this->foreignKeyExists('dossie_tipos', 'dossie_tipos_empresa_id_foreign')) {
            Schema::table('dossie_tipos', function (Blueprint $table) {
                $table->dropForeign('dossie_tipos_empresa_id_foreign');
            });
        }

        $this->dropIndexIfExists('dossie_tipos', 'dossie_tipos_empresa_tipo_idx');
        $this->dropIndexIfExists('dossie_tipos', 'dossie_tipos_empresa_chave_idx');
        $this->dropIndexIfExists('dossie_tipos', 'dossie_tipos_empresa_ativo_ordem_idx');

        Schema::table('dossie_tipos', function (Blueprint $table) {
            $table->dropColumn('empresa_id');
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $exists = DB::selectOne(
            'SELECT COUNT(1) AS total
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND index_name = ?',
            [$table, $index]
        );

        return $exists && (int) $exists->total > 0;
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $exists = DB::selectOne(
            'SELECT COUNT(1) AS total
             FROM information_schema.table_constraints
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND constraint_name = ?
               AND constraint_type = ?',
            [$table, $constraint, 'FOREIGN KEY']
        );

        return $exists && (int) $exists->total > 0;
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (!$this->indexExists($table, $index)) {
            return;
        }

        // Unique e index não-unique: tenta dropUnique e, se falhar, dropIndex.
        try {
            Schema::table($table, function (Blueprint $blueprint) use ($index) {
                $blueprint->dropUnique($index);
            });
        } catch (\Throwable $e) {
            Schema::table($table, function (Blueprint $blueprint) use ($index) {
                $blueprint->dropIndex($index);
            });
        }
    }
}
