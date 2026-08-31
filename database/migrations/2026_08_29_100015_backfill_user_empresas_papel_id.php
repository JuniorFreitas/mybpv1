<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Backfill: cada vínculo (user, empresa) criado na Fase 3 (espelho de
     * users.empresa_id) recebe o mesmo papel que o usuário já tinha em
     * users.grupo_id — zero mudança de comportamento pra quem só tem 1
     * empresa. Concessões extras (Fase 3, migration 100012) ficam sem papel
     * até serem corrigidas manualmente na próxima migration.
     */
    public function up(): void
    {
        DB::statement('
            UPDATE user_empresas ue
            INNER JOIN users u ON u.id = ue.user_id
            SET ue.papel_id = u.grupo_id
            WHERE ue.papel_id IS NULL AND ue.empresa_id = u.empresa_id
        ');
    }

    public function down(): void
    {
        DB::table('user_empresas')->update(['papel_id' => null]);
    }
};
