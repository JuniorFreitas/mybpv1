<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterFornecedoresTableRemoveAutoincrement extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fornecedores', function (Blueprint $table) {
            // 1. Remover a foreign key existente se ela existir
            try {
                $table->dropForeign(['id']);
            } catch (Exception $e) {
                // Foreign key pode não existir ainda
            }
        });

        // 2. Verificar se existem dados inconsistentes antes da alteração
        $inconsistentData = DB::table('fornecedores')
            ->whereNotIn('id', function ($query) {
                $query->select('id')->from('users');
            })
            ->count();

        if ($inconsistentData > 0) {
            throw new Exception("Existem {$inconsistentData} registros em fornecedores que não correspondem a usuários válidos. Corrija antes de executar a migration.");
        }

        Schema::table('fornecedores', function (Blueprint $table) {
            // 3. Remover auto increment e definir como bigInteger unsigned
            $table->unsignedBigInteger('id')->change();
            $table->unsignedBigInteger('empresa_id')->after('id')->nullable();

            // 4. Adicionar a foreign key correta
            $table->foreign('id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('empresa_id')->references('id')->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            // 5. Garantir que seja único (relacionamento 1:1)
            // O índice único já existe por ser PRIMARY KEY
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fornecedores', function (Blueprint $table) {
            // 1. Remover a foreign key
            $table->dropForeign(['id']);
            $table->dropForeign(['empresa_id']);
        });

        // 2. Recriar como auto increment
        Schema::table('fornecedores', function (Blueprint $table) {
            $table->id()->change();
            $table->dropColumn('empresa_id');
        });

        // Nota: No rollback, você perderá a relação direta com users
        // Os dados podem ficar inconsistentes
    }
}
