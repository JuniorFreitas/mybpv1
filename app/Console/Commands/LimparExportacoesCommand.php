<?php

namespace App\Console\Commands;

use App\Jobs\JobDeletaExportacaoExcel;
use Illuminate\Console\Command;

/**
 * Limpa a lista "Meus Downloads" (exportacoes com mais de 1 dia).
 *
 * Uso:
 *   php artisan mybp:limpar-exportacoes
 *   docker compose exec mybpdp php artisan mybp:limpar-exportacoes
 */
class LimparExportacoesCommand extends Command
{
    protected $signature = 'mybp:limpar-exportacoes
                            {--queue : Enfileira o job em vez de executar agora}';

    protected $description = 'Remove exportacoes antigas da lista Meus Downloads (JobDeletaExportacaoExcel)';

    public function handle(): int
    {
        if ($this->option('queue')) {
            JobDeletaExportacaoExcel::dispatch();
            $this->info('JobDeletaExportacaoExcel enfileirado.');

            return self::SUCCESS;
        }

        $this->info('Limpando exportacoes com mais de 1 dia...');
        (new JobDeletaExportacaoExcel())();
        $this->info('Concluido.');

        return self::SUCCESS;
    }
}
