<?php

namespace App\Console;

use App\Jobs\Admissao\Historico\AvaliacaoNoventaVencimento\AvaliacaoNoventaVencimentoJob;
use App\Jobs\controle_ponto\VerificaJornadasJob;
use App\Jobs\JobDeletaExportacaoExcel;
use App\Jobs\Movimentacao\FeriasPrevista\VerificaSaidaFeriasJob;
use App\Jobs\Movimentacao\FeriasPrevista\VerificaVencimentoFeriasJob;
use App\Jobs\Rotinas\JobAniversariantesDia;
use App\Jobs\Rotinas\JobCalculoAvos;
use App\Jobs\Rotinas\JobConvocacaoIntermitente;
use App\Jobs\Rotinas\JobCorrigePonto;
use App\Jobs\Rotinas\JobFerias;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('inspire')->hourly();
        //$schedule->command('inspire')->everyMinute();

        // Jobs com execução em apenas um servidor
        $schedule->call(new VerificaJornadasJob)->daily()->name('VerificaJornadasJob')->onOneServer();
        $schedule->call(new VerificaVencimentoFeriasJob)->monthly()->name('VerificaVencimentoFeriasJob')->onOneServer();
        $schedule->call(new VerificaSaidaFeriasJob)->monthly()->name('VerificaSaidaFeriasJob')->onOneServer();
        $schedule->call(new AvaliacaoNoventaVencimentoJob)->daily()->name('AvaliacaoNoventaVencimentoJob')->onOneServer();
        $schedule->call(new JobDeletaExportacaoExcel)->daily()->name('JobDeletaExportacaoExcel')->onOneServer();
        $schedule->call(new JobAniversariantesDia)->daily()->name('JobAniversariantesDia')->onOneServer();
        $schedule->call(new JobConvocacaoIntermitente())->hourly()->name('JobConvocacaoIntermitente')->onOneServer();
        $schedule->call(new JobFerias())->daily()->name('JobFerias')->onOneServer();
        $schedule->call(new JobCalculoAvos())->weekly()->name('JobCalculoAvos')->onOneServer();
        $schedule->call(new JobCorrigePonto())->daily()->name('JobCorrigePonto')->onOneServer();

        // Comandos Artisan com execução em apenas um servidor
        $schedule->command('horizon:snapshot')->everyFiveMinutes()->name('horizon_snapshot')->onOneServer();
        $schedule->command('mybp:vencimentoAso')->daily()->name('mybp_vencimentoAso')->onOneServer();
        $schedule->command('mybp:ferias')->daily()->name('mybp_ferias')->onOneServer();
        $schedule->command('mybp:treinamento-vencimento --chunk-size=2000 --lote-size=100')
            ->fridays()
            ->at('00:00')
            ->name('mybp_treinamento_vencimento')
            ->onOneServer();

        $schedule->command('mybp:avaliacao-experiencia')
        ->mondays()
        ->at('00:00')
        ->name('mybp_avaliacao_experiencia')
        ->onOneServer();

        $schedule->command('mybp:avaliacao-pendencias')
            ->dailyAt('07:00')
            ->name('mybp_avaliacao_pendencias')
            ->onOneServer();

        $schedule->command('mybp:encerrar-avaliacoes-vencidas')
            ->dailyAt('00:10')
            ->name('mybp_encerrar_avaliacoes_vencidas')
            ->onOneServer();

        // $schedule->command('cbo:importar')
        //     ->monthlyOn(1, '03:00')
        //     ->withoutOverlapping()
        //     ->runInBackground()
        //     ->name('cbo_importar')
        //     ->onOneServer();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

}
