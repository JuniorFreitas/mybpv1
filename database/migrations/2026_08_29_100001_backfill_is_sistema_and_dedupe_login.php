<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Marca como is_sistema=true todo usuário cujo login é o placeholder
     * 'sistema@mybp.com.br' (ou a variante com espaço 'sistema@mybp.com. br',
     * bug de digitação encontrado na auditoria), e gera um login sintético
     * único por linha para não colidir com o unique() que será adicionado
     * em seguida a users.login.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereIn('login', ['sistema@mybp.com.br', 'sistema@mybp.com. br'])
            ->orderBy('id')
            ->select('id')
            ->chunkById(500, function ($rows) {
                foreach ($rows as $row) {
                    DB::table('users')
                        ->where('id', $row->id)
                        ->update([
                            'is_sistema' => true,
                            'login' => 'sistema+' . $row->id . '@mybp.com.br',
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Irreversível de forma segura (não há como recuperar o login
        // original compartilhado sem perder a garantia de unicidade).
    }
};
