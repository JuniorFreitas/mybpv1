<?php

namespace App\Console;

use App\Jobs\Admissao\Historico\AvaliacaoNoventaVencimento\AvaliacaoNoventaVencimentoJob;
use App\Jobs\controle_ponto\VerificaJornadasJob;
use App\Jobs\JobDeletaExportacaoExcel;
use App\Jobs\Movimentacao\FeriasPrevista\VerificaSaidaFeriasJob;
use App\Jobs\Movimentacao\FeriasPrevista\VerificaVencimentoFeriasJob;
use App\Jobs\Rotinas\JobAniversariantesDia;
use App\Jobs\Rotinas\JobConvocacaoIntermitente;
use App\Jobs\Weekly_report\LembreteTarefaJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

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
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('inspire')->hourly();
        //$schedule->command('inspire')->everyMinute();

        $schedule->call(new LembreteTarefaJob)->everyMinute();
//        $schedule->call(new VerificaJornadasJob)->everyMinute();
        $schedule->call(new VerificaJornadasJob)->daily();
        $schedule->call(new VerificaVencimentoFeriasJob)->monthly();
        $schedule->call(new VerificaSaidaFeriasJob)->monthly();
        $schedule->call(new AvaliacaoNoventaVencimentoJob)->daily();
        $schedule->call(new JobDeletaExportacaoExcel)->daily();
        $schedule->call(new JobAniversariantesDia)->daily();
        $schedule->call(new JobConvocacaoIntermitente())->hourly();
//        $schedule->call(new Im)->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
