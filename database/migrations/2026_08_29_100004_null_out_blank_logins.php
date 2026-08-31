<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Unique index não permite múltiplas linhas com login='' (string vazia
     * conta como valor). NULL, ao contrário, pode se repetir livremente.
     * Sem isso o unique() da próxima migration falharia de cara.
     */
    public function up(): void
    {
        DB::table('users')->where('login', '')->update(['login' => null]);
    }

    public function down(): void
    {
        // Não reversível de forma útil — não há como distinguir depois quais
        // NULLs eram '' antes desta migration.
    }
};
