<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use MasterTag\DataHora;

class VencimentoAsoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mybp:vencimentoAso';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Atualiza Vencimento de ASO';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        set_time_limit(0);
        \DB::table('examesesmts')
            ->where('data_vencimento','<',(new DataHora())->dataInsert())
            ->update([
                'vencido' => 1,
            ]);
    }
}
