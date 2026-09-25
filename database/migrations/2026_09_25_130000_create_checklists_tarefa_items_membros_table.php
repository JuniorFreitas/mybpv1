<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checklists_tarefa_items_membros', function (Blueprint $table) {
            $table->unsignedBigInteger('checklists_tarefa_item_id');
            $table->unsignedBigInteger('user_id');

            $table->primary(
                ['checklists_tarefa_item_id', 'user_id'],
                'ck_items_membros_pk'
            );

            $table->foreign('checklists_tarefa_item_id', 'ck_items_membros_item_fk')
                ->references('id')
                ->on('checklists_tarefa_items')
                ->onUpdate('RESTRICT')
                ->onDelete('CASCADE');

            $table->foreign('user_id', 'ck_items_membros_user_fk')
                ->references('id')
                ->on('users')
                ->onUpdate('RESTRICT')
                ->onDelete('CASCADE');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checklists_tarefa_items_membros');
    }
};
