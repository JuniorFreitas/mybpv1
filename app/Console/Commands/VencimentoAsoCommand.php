<?php

namespace App\Console\Commands;

use App\Jobs\Admissao\Processo\VencimentoAsoJob;
use Illuminate\Console\Command;
use MasterTag\DataHora;

class VencimentoAsoCommand extends Command
{
    protected $signature = 'mybp:vencimentoAso {--somente-flag : Apenas marca examesesmts.vencido sem enviar e-mail} {--somente-email : Apenas envia e-mails de alerta}';

    protected $description = 'Marca ASOs vencidos e envia e-mail de alerta (mesma regra da tela/Excel)';

    public function handle(): int
    {
        set_time_limit(0);

        $somenteFlag = (bool) $this->option('somente-flag');
        $somenteEmail = (bool) $this->option('somente-email');

        if (!$somenteEmail) {
            $this->info('Atualizando flag vencido em examesesmts...');
            $afetados = \DB::table('examesesmts')
                ->where('data_vencimento', '<', (new DataHora())->dataInsert())
                ->where(function ($q) {
                    $q->where('vencido', 0)->orWhereNull('vencido');
                })
                ->update([
                    'vencido' => 1,
                ]);
            $this->info("Registros marcados como vencido: {$afetados}");
        }

        if (!$somenteFlag) {
            $this->info('Processando alertas de vencimento ASO (regra única tela/Excel)...');
            (new VencimentoAsoJob())->handle();
            $this->info('Alertas processados.');
        }

        return self::SUCCESS;
    }
}
