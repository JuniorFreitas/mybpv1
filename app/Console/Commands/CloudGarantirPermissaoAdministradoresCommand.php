<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CloudGarantirPermissaoAdministradoresCommand extends Command
{
    protected $signature = 'cloud:garantir-permissao-administradores
                            {--empresa_id= : Limita a sincronização a uma empresa específica}
                            {--dry-run : Apenas mostra quantos registros seriam inseridos}';

    protected $description = 'Garante permissão do grupo Administradores em todos os arquivos e pastas do Cloud';

    public function handle(): int
    {
        $empresaId = $this->option('empresa_id');
        $dryRun = (bool) $this->option('dry-run');

        $selectSql = '
            SELECT i.id AS item_id, g.id AS grupo_cloud_id
            FROM itens_cloud i
            INNER JOIN clouds c ON c.id = i.cloud_id
            INNER JOIN grupo_clouds g
                ON g.empresa_id = c.empresa_id
                AND g.nome = ?
            WHERE i.deleted_at IS NULL
              AND NOT EXISTS (
                    SELECT 1
                    FROM permissoes_itens_clouds p
                    WHERE p.item_id = i.id
                      AND p.grupo_cloud_id = g.id
              )
        ';

        $bindings = ['Administradores'];

        if ($empresaId) {
            $selectSql .= ' AND c.empresa_id = ?';
            $bindings[] = $empresaId;
        }

        $pendentes = DB::select($selectSql, $bindings);
        $total = count($pendentes);

        if ($total === 0) {
            $this->info('Nenhum item sem permissão de Administradores.');
            return self::SUCCESS;
        }

        if ($dryRun) {
            $this->warn("Dry-run: {$total} permissão(ões) seriam inseridas.");
            return self::SUCCESS;
        }

        $inseridos = 0;
        foreach (array_chunk($pendentes, 500) as $lote) {
            $rows = array_map(static function ($row) {
                return [
                    'item_id' => $row->item_id,
                    'grupo_cloud_id' => $row->grupo_cloud_id,
                ];
            }, $lote);

            DB::table('permissoes_itens_clouds')->insertOrIgnore($rows);
            $inseridos += count($rows);
        }

        $this->info("Permissão de Administradores garantida em {$inseridos} item(ns).");

        return self::SUCCESS;
    }
}
