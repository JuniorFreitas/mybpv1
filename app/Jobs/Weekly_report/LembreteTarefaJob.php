<?php

namespace App\Jobs\Weekly_report;

use App\Mail\Weekly_report\LembreteTarefaMail;
use App\Models\Tarefa;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;
use MasterTag\DataHora;

class LembreteTarefaJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct() {

    }

    //Comentar esse metodo se for usar com Job
    public function __invoke()
    {
        $this->handle();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle() {
        $agora = new DataHora();
        $agora->setSegundo(0);
        $inicio = $agora->dataHoraInsert();
        $agora->setSegundo(59);
        $fim = $agora->dataHoraInsert();

        //buscar todos os lembrete
        $tarefaParaLembrar = Tarefa::whereBetween('lembrete',[$inicio,$fim])->whereHas('Membros')->whereConcluido(false)->get();
        foreach ($tarefaParaLembrar as $tarefa){
            foreach ($tarefa->Membros as $usuario){
                Mail::send(new LembreteTarefaMail([
                    'para' => $usuario,
                    'modelTarefa' => $tarefa
                ]));
            }

        }

    }
}
