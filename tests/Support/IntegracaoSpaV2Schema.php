<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Aplica migrations mínimas para testes da API v2/integracao em SQLite (:memory:).
 * Cada teste recebe um banco em memória novo; não usar flag estática entre testes.
 */
trait IntegracaoSpaV2Schema
{
    protected function garantirSchemaIntegracaoSpaV2(): void
    {
        if (Schema::hasTable('vagas_abertas') && Schema::hasColumn('vagas_abertas', 'empresa_id')) {
            return;
        }

        $paths = [
            'database/migrations/2021_07_05_220855_create_areas_table.php',
            'database/migrations/2021_07_05_220855_create_users_table.php',
            'database/migrations/2021_07_05_220855_create_arquivos_table.php',
            'database/migrations/2021_10_10_000523_add_field_disco_table_arquivos.php',
            'database/migrations/2021_07_05_220912_add_foreign_keys_to_arquivos_table.php',
            'database/migrations/2021_07_05_220855_create_clientes_table.php',
            'database/migrations/2021_07_05_220855_create_cliente_logotipo_table.php',
            'database/migrations/2021_07_05_220912_add_foreign_keys_to_clientes_table.php',
            'database/migrations/2021_07_05_220912_add_foreign_keys_to_cliente_logotipo_table.php',
            'database/migrations/2022_03_25_102455_add_campos_clientes_table.php',
            'database/migrations/2021_07_05_220855_create_municipios_table.php',
            'database/migrations/2021_07_05_220855_create_vagas_table.php',
            'database/migrations/2021_07_05_220855_create_vagas_abertas_table.php',
            'database/migrations/2021_07_05_220855_create_grupo_clouds_table.php',
            'database/migrations/2021_07_05_220912_add_foreign_keys_to_vagas_abertas_table.php',
            'database/migrations/2021_10_11_104304_add_titulo_vaga_abertas_table.php',
            'database/migrations/2022_04_21_140151_add_campos_vagas_abertas_table.php',
            'database/migrations/2021_10_01_103825_add_empresa_id_novos_table.php',
        ];

        foreach ($paths as $path) {
            $this->artisan('migrate', [
                '--path' => $path,
                '--force' => true,
            ]);
        }
    }

    protected function limparDadosIntegracaoSpaV2(): void
    {
        if (! Schema::hasTable('vagas_abertas')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF');
        }

        if (Schema::hasTable('cliente_logotipo')) {
            DB::table('cliente_logotipo')->delete();
        }
        if (Schema::hasTable('arquivos')) {
            DB::table('arquivos')->delete();
        }
        DB::table('vagas_abertas')->delete();
        DB::table('vagas')->delete();
        DB::table('clientes')->delete();
        DB::table('users')->where('id', '>=', 90000)->delete();

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON');
        }
    }
}
