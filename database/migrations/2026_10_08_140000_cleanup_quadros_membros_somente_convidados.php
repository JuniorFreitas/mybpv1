<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * O backfill inicial colocava todo usuário com skill weekly_report como membro
 * de todos os quadros da empresa. A regra correta (Trello) é: só vê o quadro
 * quem é dono ou foi convidado via Compartilhar.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('quadros_membros')) {
            return;
        }

        // Mantém apenas donos; membros passam a entrar só via Share
        DB::table('quadros_membros')
            ->where('papel', '!=', 'dono')
            ->delete();
    }

    public function down(): void
    {
        // Irreversível: não recria o backfill amplo de membros
    }
};
