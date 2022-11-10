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

class VerificaSaidaFeriasJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public function __construct()
    {

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
    public function handle()
    {
        try {
        $agora = new DataHora();
        $inicio = $agora->dataInsert();
        $ultimoDiaMes = $agora->ultimoDiaMes();
        $mes = $agora->mes();
        $ano = $agora->ano();
        $dataHora = $ano . '-' . $mes . '-' . $ultimoDiaMes;
        $fim = new DataHora($dataHora);
        $fim = $fim->dataInsert();

        $listaDeEmprasaID = Sistema::listaEmpresas();

        foreach ($listaDeEmprasaID as $empresa_id) {

            $tarefaParaLembrar = FeriasPrevista::withoutGlobalScopes()->whereEmpresaId($empresa_id)
                ->whereBetween('data_saida', [$inicio, $fim])
                ->with(['Colaborador' => function ($q) {
                    $q->withoutGlobalScopes();
                }])->get();

            $usuarios = User::withoutGlobalScopes()->whereEmpresaId($empresa_id)
                ->select(['id', 'nome', 'login'])
                ->whereIn('tipo', User::TIPOS_USUARIOS_GERENCIAIS)
                ->whereHas('UserRecebeEmail', function ($q) {
                    $q->where('nome', 'Vencimento Férias');
                    $q->where('ativo', true);
                })
                ->with(['UserRecebeEmail' => function ($q) {
                    $q->where('nome', 'Vencimento Férias');
                    $q->where('ativo', true);
                }]);

            foreach ($usuarios as $usuario) {
                if (!empty($tarefaParaLembrar)) {
                    Mail::send(new SaidaMail([
                        'usuario' => $usuario,
                        'vencimento' => $tarefaParaLembrar,
                        'empresa_id' => $empresa_id
                    ]));
                    \Log::info("E-mail de Verificar Saida de Ferias enviado com sucesso - {$usuario['nome']} - {$empresa_id} horario: {$agora}");

                }
            }
        }
        } catch (\Exception $e) {
            \Log::error($e->getFile() . " - " . $e->getMessage() . " - " . $e->getCode() . ' Verifica Saida Ferias');
        }
    }
}
