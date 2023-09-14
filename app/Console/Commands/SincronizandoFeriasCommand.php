<?php

namespace App\Console\Commands;

use App\Models\Ferias;
use Illuminate\Console\Command;
use Log;
use MasterTag\DataHora;

class SincronizandoFeriasCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mybp:ferias';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincronizando Ferias';

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
        try {
            \DB::table('ferias')
                ->where('status_aprovacao_gestor', Ferias::STATUS_APROVADO)
                ->where('aprovado_via_script', true)
                ->where('data_saida', '<=', (new DataHora())->dataInsert())
                ->where('data_retorno', '>=', (new DataHora())->dataInsert())
                ->update([
                    'status_ferias' => Ferias::STATUS_GOZANDO,
                    'data_status_ferias' => now()
                ]);
            Log::info("Ferias gozando");

            \DB::table('ferias')
                ->where('status_aprovacao_rh', Ferias::STATUS_APROVADO)
                ->where('data_saida', '<=', (new DataHora())->dataInsert())
                ->where('data_retorno', '>=', (new DataHora())->dataInsert())
                ->update([
                    'status_ferias' => Ferias::STATUS_GOZANDO,
                    'data_status_ferias' => now()
                ]);
            Log::info("Ferias gozando rh");

            \DB::table('ferias')
                ->where('status_aprovacao_gestor', Ferias::STATUS_APROVADO)
                ->where('aprovado_via_script', true)
                ->where('data_retorno', '<', (new DataHora())->dataInsert())
                ->update([
                    'status_ferias' => Ferias::STATUS_GOZADA,
                    'data_status_ferias' => now()
                ]);

            Log::info("Ferias gozadas");

            \DB::table('ferias')
                ->where('status_aprovacao_rh', Ferias::STATUS_APROVADO)
                ->where('data_retorno', '<', (new DataHora())->dataInsert())
                ->update([
                    'status_ferias' => Ferias::STATUS_GOZADA,
                    'data_status_ferias' => now()
                ]);

            Log::info("Ferias gozando rh");

            // NAO FAZER AGORA - AGUARDANDO DEFINICAO DE REGRAS DE NEGOCIO (DANY)
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }
    }
}
