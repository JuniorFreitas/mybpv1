<?php

namespace App\Console\Commands;

use App\Jobs\Rotinas\JobAniversariantesDia;
use Illuminate\Console\Command;

class DispararAniversariantesCommand extends Command
{
    protected $signature = 'mybp:aniversariantes
                            {--queue : Enfileira o job em vez de executar agora}';

    protected $description = 'Dispara o envio dos aniversariantes do dia';

    public function handle(): int
    {
        if ($this->option('queue')) {
            JobAniversariantesDia::dispatch();
            $this->info('Envio de aniversariantes enfileirado.');

            return self::SUCCESS;
        }

        $this->info('Disparando o envio dos aniversariantes do dia...');
        (new JobAniversariantesDia())();
        $this->info('Envio de aniversariantes concluido.');

        return self::SUCCESS;
    }
}