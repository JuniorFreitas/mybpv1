<?php

namespace App\Jobs\Admissao\Processo;

use App\Mail\Admissao\Processo\VencimentoAsoMail;
use App\Models\ClienteConfig;
use App\Models\FeedbackCurriculo;
use App\Models\Sistema;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;
use MasterTag\DataHora;

class VencimentoAsoJob implements ShouldQueue
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
        $listaDeEmprasaID = Sistema::listaEmpresas();

        foreach ($listaDeEmprasaID as $empresa_id) {

            $cliente = ClienteConfig::whereClienteId($empresa_id)->first();

            if (!empty($cliente)) {

                $agora = new DataHora();
                $addMes = $agora->addMes($cliente->vencimento_aso);
                $addMes = new DataHora($addMes);
                $addMes = $addMes->dataInsert();


                $vencimentoParaLembrar = FeedbackCurriculo::withoutGlobalScopes()->where('empresa_id', $empresa_id)
                    ->whereHas('Admissao', function ($q) use ($addMes) {
                        $q->whereDataAso($addMes);
                    })->with(['Admissao' => function ($q) use ($addMes) {
                        $q->whereDataAso($addMes);
                    }, 'Curriculo'])
                    ->get();

                $usuarios = User::withoutGlobalScopes()->whereEmpresaId($empresa_id)
                    ->whereAtivo(true)
                    ->whereHas('UserRecebeEmail', function ($q) {
                        $q->where('nome', 'Vencimento ASO');
                        $q->where('ativo', true);
                    })
                    ->with(['UserRecebeEmail' => function ($q) {
                        $q->where('nome', 'Vencimento ASO');
                        $q->where('ativo', true);
                    }])
                    ->get(['id', 'nome', 'login']);

                foreach ($usuarios as $usuario) {
                    $vencimentos = array();
                    $dados = array();
                    foreach ($vencimentoParaLembrar as $vencimento) {

                        $dados['colaborador'] = $vencimento->Curriculo->nome;
                        $dados['data_aso'] = $vencimento->Admissao->data_aso;

                        if (!empty($dados)) {
                            array_push($vencimentos, $dados);
                        }
                    }

                    if (!empty($vencimentos)) {
                        \Mail::send(new VencimentoAsoMail([
                            'usuario' => $usuario,
                            'vencimentos' => $vencimentos,
                            'empresa_id' => $empresa_id,
                        ]));
                    }
                }
            }
        }
    }
}
