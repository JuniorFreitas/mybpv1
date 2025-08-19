<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class createIndicesTreinamentos extends Migration
{
    /**
     * Adiciona índices para otimizar performance do comando TreinamentoVencimento
     */
    public function up(): void
    {
        // Índices para tabela admissoes
        if (!$this->indexExists('admissoes', 'idx_status_feedback')) {
            Schema::table('admissoes', function (Blueprint $table) {
                $table->index(['status', 'feedback_id'], 'idx_status_feedback');
            });
        }

        if (!$this->indexExists('admissoes', 'idx_centro_custo_filial')) {
            Schema::table('admissoes', function (Blueprint $table) {
                $table->index('centro_custo_filial_id', 'idx_centro_custo_filial');
            });
        }

        // Índices para tabela feedback_curriculos
        if (!$this->indexExists('feedback_curriculos', 'idx_empresa_id_optimized')) {
            Schema::table('feedback_curriculos', function (Blueprint $table) {
                $table->index('empresa_id', 'idx_empresa_id_optimized');
            });
        }

        // Índices para tabela treinamentos
        if (!$this->indexExists('treinamentos', 'idx_feedback_id_optimized')) {
            Schema::table('treinamentos', function (Blueprint $table) {
                $table->index('feedback_id', 'idx_feedback_id_optimized');
            });
        }

        // Índices para tabela treinamento_vencimento
        if (!$this->indexExists('treinamento_vencimento', 'idx_treinamento_data_venc')) {
            Schema::table('treinamento_vencimento', function (Blueprint $table) {
                $table->index(['treinamento_id', 'data_vencimento'], 'idx_treinamento_data_venc');
            });
        }

        if (!$this->indexExists('treinamento_vencimento', 'idx_vencimento_id_optimized')) {
            Schema::table('treinamento_vencimento', function (Blueprint $table) {
                $table->index('vencimento_id', 'idx_vencimento_id_optimized');
            });
        }

        // Índices para tabela users
        if (!$this->indexExists('users', 'idx_empresa_ativo_email')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index(['empresa_id', 'ativo'], 'idx_empresa_ativo_email');
            });
        }

        // Índices para tabela user_recebe_email
        if (!$this->indexExists('user_recebe_email', 'idx_tipo_ativo_user')) {
            Schema::table('user_recebe_email', function (Blueprint $table) {
                $table->index(['tipo_email_id', 'ativo', 'user_id'], 'idx_tipo_ativo_user');
            });
        }

        // Índices para tabela vencimentos
        if (!$this->indexExists('vencimentos', 'idx_empresa_ativo_venc')) {
            Schema::table('vencimentos', function (Blueprint $table) {
                $table->index(['empresa_id', 'ativo'], 'idx_empresa_ativo_venc');
            });
        }

        // Índices para tabela centro_custos
        if (!$this->indexExists('centro_custos', 'idx_centro_custo_id_label')) {
            Schema::table('centro_custos', function (Blueprint $table) {
                $table->index(['id', 'label'], 'idx_centro_custo_id_label');
            });
        }

        // Índices para tabela curriculos
        if (!$this->indexExists('curriculos', 'idx_curriculo_id_nome_cpf')) {
            Schema::table('curriculos', function (Blueprint $table) {
                $table->index(['id', 'nome', 'cpf'], 'idx_curriculo_id_nome_cpf');
            });
        }

        // Adicionar índices compostos avançados para queries mais complexas
        $this->addAdvancedIndexes();

        // Log das otimizações
        \Illuminate\Support\Facades\Log::info('Índices de performance para TreinamentoVencimento criados com sucesso');
    }

    /**
     * Remove os índices criados
     */
    public function down(): void
    {
        $indexes = [
            'admissoes' => ['idx_status_feedback', 'idx_centro_custo_filial'],
            'feedback_curriculos' => ['idx_empresa_id_optimized'],
            'treinamentos' => ['idx_feedback_id_optimized'],
            'treinamento_vencimento' => ['idx_treinamento_data_venc', 'idx_vencimento_id_optimized'],
            'users' => ['idx_empresa_ativo_email'],
            'user_recebe_email' => ['idx_tipo_ativo_user'],
            'vencimentos' => ['idx_empresa_ativo_venc'],
            'centro_custos' => ['idx_centro_custo_id_label'],
            'curriculos' => ['idx_curriculo_id_nome_cpf'],
        ];

        foreach ($indexes as $table => $tableIndexes) {
            foreach ($tableIndexes as $index) {
                if ($this->indexExists($table, $index)) {
                    Schema::table($table, function (Blueprint $table) use ($index) {
                        $table->dropIndex($index);
                    });
                }
            }
        }

        // Remove índices avançados
        $this->removeAdvancedIndexes();
    }

    /**
     * Adiciona índices compostos avançados para queries específicas
     */
    private function addAdvancedIndexes(): void
    {
        try {
            // Índice composto para a query principal de admitidos
            DB::statement('
                CREATE INDEX IF NOT EXISTS idx_admissoes_complete_query
                ON admissoes (status, feedback_id, centro_custo_id, centro_custo_filial_id)
            ');

            // Índice para filtro de data de vencimento
            DB::statement('
                CREATE INDEX IF NOT EXISTS idx_treinamento_vencimento_date_filter
                ON treinamento_vencimento (data_vencimento, treinamento_id, vencimento_id)
            ');

            // Índice para JOIN entre treinamentos e treinamento_vencimento
            DB::statement('
                CREATE INDEX IF NOT EXISTS idx_treinamentos_vencimento_join
                ON treinamentos (id, feedback_id)
            ');

            // Índice para covering query de usuários email
            DB::statement('
                CREATE INDEX IF NOT EXISTS idx_users_email_covering
                ON users (empresa_id, ativo, id, nome, login)
            ');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Erro ao criar índices avançados', [
                'erro' => $e->getMessage()
            ]);
        }
    }

    /**
     * Remove índices avançados
     */
    private function removeAdvancedIndexes(): void
    {
        $advancedIndexes = [
            'idx_admissoes_complete_query',
            'idx_treinamento_vencimento_date_filter',
            'idx_treinamentos_vencimento_join',
            'idx_users_email_covering'
        ];

        foreach ($advancedIndexes as $index) {
            try {
                DB::statement("DROP INDEX IF EXISTS {$index}");
            } catch (\Exception $e) {
                // Ignora erros ao remover índices
            }
        }
    }

    /**
     * Verifica se um índice existe
     */
    private function indexExists(string $table, string $index): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$index]);
            return !empty($indexes);
        } catch (\Exception $e) {
            return false;
        }
    }
}
