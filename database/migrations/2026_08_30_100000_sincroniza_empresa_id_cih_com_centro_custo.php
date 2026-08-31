<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * cihs.empresa_id estava fora de sincronia com o centro_custo_id que
     * referencia (que já foi corrigido pra empresa nova quando ligado a
     * filial, na migration 2026_08_30_090000). ata_reuniaos já estava
     * sincronizada (checado antes de aplicar, 0 linhas divergentes).
     */
    public function up(): void
    {
        DB::statement("
            UPDATE cihs c
            INNER JOIN centro_custos cc ON cc.id = c.centro_custo_id
            SET c.empresa_id = cc.empresa_id
            WHERE cc.empresa_id != c.empresa_id
        ");
    }

    public function down(): void
    {
        // Não reversível de forma segura.
    }
};
