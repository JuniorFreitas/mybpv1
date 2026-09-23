<?php

namespace App\Console\Commands;

use App\Jobs\Rotinas\JobAniversariantesDia;
use App\Services\Aniversariante\AniversarianteEnvioDiaService;
use Illuminate\Console\Command;

class DispararAniversariantesCommand extends Command
{
    protected $signature = 'mybp:aniversariantes
                            {--queue : Enfileira o job em vez de executar agora}
                            {--retentar-pendentes : Reenvia registros do ano com status enviando/erro}';

    protected $description = 'Dispara o envio dos aniversariantes do dia';

    public function handle(): int
    {
        if ($this->option('queue')) {
            JobAniversariantesDia::dispatch();
            $this->info('Envio de aniversariantes enfileirado.');

            return self::SUCCESS;
        }

        $service = app(AniversarianteEnvioDiaService::class);

        if ($this->option('retentar-pendentes')) {
            $this->info('Retentando felicitações pendentes (enviando/erro) do ano...');
            $resumo = $service->retentarPendentesDoAno();
        } else {
            $this->info('Disparando o envio dos aniversariantes do dia...');
            $resumo = $service->disparar();
        }

        $this->info(sprintf(
            'Concluido. total=%d enviados=%d erros=%d ignorados=%d',
            $resumo['total'],
            $resumo['enviados'],
            $resumo['erros'],
            $resumo['ignorados']
        ));

        return self::SUCCESS;
    }
}
