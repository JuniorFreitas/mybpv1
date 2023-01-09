<?php

namespace App\Jobs\Rotinas;

use App\Mail\AniversariantesMail;
use App\Models\Admissao;
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
                ->where('status','Aberto')
                ->where('prazo_resposta_expiracao','<=',(new DataHora())->dataHoraInsert())
                ->update([
                    'status' => 'Expirado',
                    'resposta_colaborador' => 'Expirado',
                    'data_resposta_colaborador' => now()
                ]);
           \Log::info('Rotina de convocação de intermitentes executada com sucesso!');
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
        }

    }
}
