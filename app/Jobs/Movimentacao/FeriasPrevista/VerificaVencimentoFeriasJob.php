<?php

namespace App\Jobs\Movimentacao\FeriasPrevista;

use App\Mail\Movimentacao\FeriasPrevista\VencimentoMail;
use App\Models\Cliente;
use App\Models\ClienteConfig;
use App\Models\FeriasPrevista;
use App\Models\Sistema;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;
use MasterTag\DataHora;

class VerificaVencimentoFeriasJob implements ShouldQueue
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
            $listaDeEmprasaID = Sistema::listaEmpresas();
            foreach ($listaDeEmprasaID as $empresa_id) {

                $cliente = ClienteConfig::whereClienteId($empresa_id)->first();

                if (!empty($cliente)) {

                    $agora = new DataHora();
                    $agora->addMes($cliente->verifica_mes_vencimento);
                    $inicio = $agora->dataInsert();
                    $ultimoDiaMes = $agora->ultimoDiaMes();
                    $mes = $agora->mes();
                    $ano = $agora->ano();
                    $dataHora = $ano . '-' . $mes . '-' . $ultimoDiaMes;
                    $fim = new DataHora($dataHora);
                    $fim = $fim->dataInsert();

                    $tarefaParaLembrar = FeriasPrevista::withoutGlobalScopes()->whereEmpresaId($empresa_id)
                        ->whereBetween('ultima_data', [$inicio, $fim])
                        ->with(['Colaborador' => function ($q) {
                            $q->withoutGlobalScopes();
                        }])->get();

                    $usuarios = User::withoutGlobalScopes()->whereEmpresaId($empresa_id)
                        ->select(['id', 'nome', 'login'])    
                        ->whereIn('tipo', ['Administrador', 'Suporte'])
                        ->whereHas('UserRecebeEmail', function ($q) {
                            $q->where('nome', 'Vencimento Férias');
                            $q->where('ativo', true);
                        })
                        ->with(['UserRecebeEmail' => function ($q) {
                            $q->where('nome', 'Vencimento Férias');
                            $q->where('ativo', true);
                        }]);

                    foreach ($usuarios as $usuario) {
                        Mail::send(new VencimentoMail([
                            'usuario' => $usuario,
                            'vencimento' => $tarefaParaLembrar,
                            'empresa_id' => $empresa_id
                        ]));
                    \Log::info("E-mail de Verificar Vencimento de Ferias enviado com sucesso - {$usuario['nome']} - {$empresa_id} horario: {$agora}");

                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error($e->getFile() . " - " . $e->getMessage() . " - " . $e->getCode() . ' Verifica Vencimento Ferias Job');
        }
    }
}
