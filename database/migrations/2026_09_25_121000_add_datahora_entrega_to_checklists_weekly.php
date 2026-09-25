<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('checklists_tarefas', function (Blueprint $table) {
            $table->dateTime('datahora_entrega')->nullable()->after('ordem');
        });

        Schema::table('checklists_tarefa_items', function (Blueprint $table) {
            $table->dateTime('datahora_entrega')->nullable()->after('ordem');
        });
    }

    public function down(): void
    {
        Schema::table('checklists_tarefas', function (Blueprint $table) {
            $table->dropColumn('datahora_entrega');
        });

        Schema::table('checklists_tarefa_items', function (Blueprint $table) {
            $table->dropColumn('datahora_entrega');
        });
    }
};
