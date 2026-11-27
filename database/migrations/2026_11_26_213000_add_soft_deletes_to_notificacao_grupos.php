<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('notificacao_grupos')) {
            return;
        }

        if (!Schema::hasColumn('notificacao_grupos', 'deleted_at')) {
            Schema::table('notificacao_grupos', function (Blueprint $table) {
                $table->softDeletes();
                $table->index(['empresa_id', 'tipo_codigo', 'deleted_at'], 'notificacao_grupos_empresa_tipo_deleted_idx');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('notificacao_grupos') || !Schema::hasColumn('notificacao_grupos', 'deleted_at')) {
            return;
        }

        Schema::table('notificacao_grupos', function (Blueprint $table) {
            $table->dropIndex('notificacao_grupos_empresa_tipo_deleted_idx');
            $table->dropSoftDeletes();
        });
    }
};
