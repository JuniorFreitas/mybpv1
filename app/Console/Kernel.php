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
use App\Jobs\Rotinas\JobAsosVencidos;
use App\Jobs\Weekly_report\LembreteTarefaJob;
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
        $schedule->call(new LembreteTarefaJob)->everyMinute()->onOneServer();
        $schedule->call(new VerificaJornadasJob)->daily()->onOneServer();
        $schedule->call(new VerificaVencimentoFeriasJob)->monthly()->onOneServer();
        $schedule->call(new VerificaSaidaFeriasJob)->monthly()->onOneServer();
        $schedule->call(new AvaliacaoNoventaVencimentoJob)->daily()->onOneServer();
        $schedule->call(new JobDeletaExportacaoExcel)->daily()->onOneServer();
        $schedule->call(new JobAniversariantesDia)->daily()->onOneServer();
        $schedule->call(new JobConvocacaoIntermitente())->hourly()->onOneServer();
        $schedule->call(new JobFerias())->daily()->onOneServer();
        $schedule->call(new JobCalculoAvos())->weekly()->onOneServer();
        $schedule->call(new JobCorrigePonto())->daily()->onOneServer();

        // Comandos Artisan com execução em apenas um servidor
        $schedule->command('horizon:snapshot')->everyFiveMinutes()->onOneServer();
        $schedule->command('mybp:vencimentoAso')->daily()->onOneServer();
        $schedule->command('mybp:ferias')->daily()->onOneServer();
        $schedule->command('mybp:treinamento-vencimento --chunk-size=2000 --lote-size=100 --id=78862')
            ->fridays()
            ->at('00:00')
            ->onOneServer();
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
