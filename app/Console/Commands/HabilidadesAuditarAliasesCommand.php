<?php

namespace App\Console\Commands;

use App\Authorization\HabilidadeAliasConsolidator;
use Illuminate\Console\Command;

class HabilidadesAuditarAliasesCommand extends Command
{
    protected $signature = 'habilidades:auditar-aliases
                            {--csv= : Caminho opcional para exportar CSV}';

    protected $description = 'Audita pares alias/canônico do HabilidadeAliasMap no banco (pivots papeis_habilidades)';

    public function handle(HabilidadeAliasConsolidator $consolidator): int
    {
        $rows = $consolidator->auditAll();

        $this->table(
            ['Alias', 'Canônico', 'Status', 'ID alias', 'ID canônico', 'Pivots alias', 'Pivots canônico'],
            array_map(static fn (array $r) => [
                $r['alias'],
                $r['canonico'],
                $r['status'],
                $r['alias_id'] ?? '-',
                $r['canonico_id'] ?? '-',
                $r['pivots_alias'],
                $r['pivots_canonico'],
            ], $rows)
        );

        $csvPath = $this->option('csv');
        if (is_string($csvPath) && $csvPath !== '') {
            $fp = fopen($csvPath, 'w');
            if ($fp === false) {
                $this->error("Não foi possível escrever {$csvPath}");

                return self::FAILURE;
            }
            fputcsv($fp, ['alias', 'canonico', 'status', 'alias_id', 'canonico_id', 'pivots_alias', 'pivots_canonico']);
            foreach ($rows as $r) {
                fputcsv($fp, [
                    $r['alias'],
                    $r['canonico'],
                    $r['status'],
                    $r['alias_id'],
                    $r['canonico_id'],
                    $r['pivots_alias'],
                    $r['pivots_canonico'],
                ]);
            }
            fclose($fp);
            $this->info("CSV: {$csvPath}");
        }

        $problemas = array_filter($rows, static fn (array $r) => in_array($r['status'], [
            HabilidadeAliasConsolidator::STATUS_AMBOS,
            HabilidadeAliasConsolidator::STATUS_SO_ALIAS,
        ], true));

        $this->info('Pares a consolidar: ' . count($problemas));

        return self::SUCCESS;
    }
}
