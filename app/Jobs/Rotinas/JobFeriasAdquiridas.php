<?php

namespace App\Jobs\Rotinas;

use App\Models\FeriasAdquiridas;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MasterTag\DataHora;

class JobFeriasAdquiridas implements ShouldQueue
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
            $ferias_gozando = \DB::table('ferias_adquiridas')
                ->where('data_saida','<=',(new DataHora())->dataInsert())
                ->where('data_retorno','>=',(new DataHora())->dataInsert())
                ->update([
                    'status' => FeriasAdquiridas::STATUS_GOZANDO
                ]);

            $ferias_gozadas = \DB::table('ferias_adquiridas')
                ->where('data_retorno','<',(new DataHora())->dataInsert())
                ->update([
                    'status' => FeriasAdquiridas::STATUS_GOZADA
                ]);

            // NAO FAZER AGORA - AGUARDANDO DEFINICAO DE REGRAS DE NEGOCIO (DANY)
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

    }
}
