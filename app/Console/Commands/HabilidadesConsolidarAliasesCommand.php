<?php

namespace App\Console\Commands;

use App\Authorization\HabilidadeAliasConsolidator;
use App\Http\Middleware\CarregaHabilidades;
use Illuminate\Console\Command;

class HabilidadesConsolidarAliasesCommand extends Command
{
    protected $signature = 'habilidades:consolidar-aliases
                            {--apply : Grava alterações (padrão: dry-run)}';

    protected $description = 'Consolida habilidades alias duplicadas para o nome canônico (HabilidadeAliasMap)';

    public function handle(HabilidadeAliasConsolidator $consolidator): int
    {
        $apply = (bool) $this->option('apply');

        if (!$apply) {
            $this->warn('Modo dry-run. Use --apply para gravar.');
        }

        $results = $consolidator->consolidateAll($apply);

        $errors = 0;
        foreach ($results as $r) {
            $prefix = $r['ok'] ? '[ok]' : '[ERRO]';
            $this->line("{$prefix} {$r['alias']} → {$r['canonico']}: [{$r['action']}] {$r['message']}");
            if (!$r['ok']) {
                $errors++;
            }
        }

        if ($apply && $errors === 0) {
            CarregaHabilidades::forgetNomesCache();
            $this->call('habilidades:sync-catalog');
        }

        if ($errors > 0) {
            $this->error("Concluído com {$errors} erro(s).");

            return self::FAILURE;
        }

        $this->info($apply ? 'Consolidação aplicada.' : 'Dry-run concluído.');

        return self::SUCCESS;
    }
}
