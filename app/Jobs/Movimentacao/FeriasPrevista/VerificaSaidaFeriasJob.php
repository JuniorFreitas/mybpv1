<?php

namespace App\Jobs\Movimentacao\FeriasPrevista;

use App\Mail\Movimentacao\FeriasPrevista\SaidaMail;
use App\Mail\Movimentacao\FeriasPrevista\VencimentoMail;
use App\Mail\Weekly_report\LembreteTarefaMail;
use App\Models\FeriasPrevista;
use App\Models\Sistema;
use App\Models\Tarefa;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;
use MasterTag\DataHora;

class VerificaSaidaFeriasJob implements ShouldQueue {
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $agora->addMes(1);
        $inicio = $agora->dataHoraInsert();
        $ultimoDiaMes = $agora->ultimoDiaMes();
        $mes = $agora->mes();
        $ano = $agora->ano();
        $dataHora = $ano . '-' . $mes . '-' . $ultimoDiaMes . ' 23:59:59';
        $fim = new DataHora($dataHora);
        $fim = $fim->dataHoraInsert();

        $listaDeEmprasaID = Sistema::listaEmpresas();

        foreach ($listaDeEmprasaID as $empresa_id) {

            $tarefaParaLembrar = FeriasPrevista::withoutGlobalScopes()->whereEmpresaId($empresa_id)
                ->whereBetween('data_saida', [$inicio, $fim])
                ->with('Colaborador')->get();

            //PILLAR EXCLUSIVO
            $usuario = User::find(39766);
            Mail::send(new SaidaMail([
                'usuario' => $usuario,
                'vencimento' => $tarefaParaLembrar
            ]));
        }
    }
}
