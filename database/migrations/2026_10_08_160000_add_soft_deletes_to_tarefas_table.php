<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tarefas', function (Blueprint $table) {
            $table->unsignedBigInteger('quem_deletou_id')->nullable()->after('user_id');
            $table->softDeletes();
            $table->index('deleted_at', 'tarefas_deleted_at_index');
            $table->index('quem_deletou_id', 'tarefas_quem_deletou_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('tarefas', function (Blueprint $table) {
            $table->dropIndex('tarefas_deleted_at_index');
            $table->dropIndex('tarefas_quem_deletou_id_index');
            $table->dropColumn(['quem_deletou_id', 'deleted_at']);
        });
    }
};
