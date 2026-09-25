<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índices de performance do Weekly Report (board, logs, lembrete).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarefas', function (Blueprint $table) {
            $table->index(['lista_id', 'ordem'], 'idx_wr_tarefas_lista_ordem');
            $table->index(['concluido', 'lembrete'], 'idx_wr_tarefas_concluido_lembrete');
        });

        Schema::table('log_weekly', function (Blueprint $table) {
            $table->index(['tarefa_id', 'created_at'], 'idx_wr_log_tarefa_created');
            $table->index(['quadro_id', 'created_at'], 'idx_wr_log_quadro_created');
        });

        Schema::table('checklists_tarefas', function (Blueprint $table) {
            $table->index(['tarefa_id', 'ordem'], 'idx_wr_ck_tarefa_ordem');
        });

        Schema::table('checklists_tarefa_items', function (Blueprint $table) {
            $table->index(['checklist_id', 'ordem'], 'idx_wr_ck_item_ordem');
        });

        Schema::table('tarefas_comentarios', function (Blueprint $table) {
            $table->index(['tarefa_id', 'tipo'], 'idx_wr_comentarios_tarefa_tipo');
        });

        Schema::table('membros_tarefa', function (Blueprint $table) {
            $table->index(['tarefa_id', 'user_id'], 'idx_wr_membros_tarefa');
        });
    }

    public function down(): void
    {
        Schema::table('tarefas', function (Blueprint $table) {
            $table->dropIndex('idx_wr_tarefas_lista_ordem');
            $table->dropIndex('idx_wr_tarefas_concluido_lembrete');
        });

        Schema::table('log_weekly', function (Blueprint $table) {
            $table->dropIndex('idx_wr_log_tarefa_created');
            $table->dropIndex('idx_wr_log_quadro_created');
        });

        Schema::table('checklists_tarefas', function (Blueprint $table) {
            $table->dropIndex('idx_wr_ck_tarefa_ordem');
        });

        Schema::table('checklists_tarefa_items', function (Blueprint $table) {
            $table->dropIndex('idx_wr_ck_item_ordem');
        });

        Schema::table('tarefas_comentarios', function (Blueprint $table) {
            $table->dropIndex('idx_wr_comentarios_tarefa_tipo');
        });

        Schema::table('membros_tarefa', function (Blueprint $table) {
            $table->dropIndex('idx_wr_membros_tarefa');
        });
    }
};
