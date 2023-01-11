<?php

namespace App\Jobs\Rotinas;

use App\Mail\AniversariantesMail;
use App\Models\Admissao;
use App\Models\Intermitente;
use App\Models\ParabensEnviado;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MasterTag\DataHora;

class JobConvocacaoIntermitente implements ShouldQueue
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
            \App\Models\Intermitente::withoutGlobalScopes()->whereNull('resposta_colaborador')
                ->where('status',Intermitente::STATUS_ABERTO)
                ->where('prazo_resposta_expiracao','<=',(new DataHora())->dataHoraInsert())
                ->update([
                    'status' => Intermitente::STATUS_EXPIRADO,
                    'resposta_colaborador' => Intermitente::STATUS_EXPIRADO,
                    'data_resposta_colaborador' => (new DataHora())->dataHoraInsert()
                ]);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

    }
}
