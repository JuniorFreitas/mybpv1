<?php

namespace App\Jobs\Rotinas;

use App\Models\Ferias;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MasterTag\DataHora;

class JobFerias implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct()
    {

    }

    public function __invoke()
    {
        $this->handle();
    }

    public function handle()
    {
        try {
            $ferias_gozando_sistema = \DB::table('ferias')
                ->whereNotNull('status_aprovacao_gestor')
                ->where('aprovado_via_script', true)
                ->where('data_saida','<=',(new DataHora())->dataInsert())
                ->where('data_retorno','>=',(new DataHora())->dataInsert())
                ->update([
                    'status_ferias' => Ferias::STATUS_GOZANDO,
                    'data_status_ferias' => (new DataHora())->dataInsert()
                ]);

            $ferias_gozando_rh = \DB::table('ferias')
                ->whereNotNull('status_aprovacao_rh')
                ->where('data_saida','<=',(new DataHora())->dataInsert())
                ->where('data_retorno','>=',(new DataHora())->dataInsert())
                ->update([
                    'status_ferias' => Ferias::STATUS_GOZANDO,
                    'data_status_ferias' => (new DataHora())->dataInsert()
                ]);

            $ferias_gozadas_sistema = \DB::table('ferias')
                ->whereNotNull('status_aprovacao_gestor')
                ->where('aprovado_via_script', true)
                ->where('data_retorno','<',(new DataHora())->dataInsert())
                ->update([
                    'status' => Ferias::STATUS_GOZADA,
                    'data_status_ferias' => (new DataHora())->dataInsert()
                ]);

            $ferias_gozadas_rh = \DB::table('ferias')
                ->whereNotNull('status_aprovacao_rh')
                ->where('aprovado_via_script', true)
                ->where('data_retorno','<',(new DataHora())->dataInsert())
                ->update([
                    'status' => Ferias::STATUS_GOZADA,
                    'data_status_ferias' => (new DataHora())->dataInsert()
                ]);

            // NAO FAZER AGORA - AGUARDANDO DEFINICAO DE REGRAS DE NEGOCIO (DANY)
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

    }
}
