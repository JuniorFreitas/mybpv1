<?php

namespace App\Jobs\Admissao\Processo;

use App\Mail\Admissao\Processo\VencimentoAsoMail;
use App\Models\Admissao;
use App\Models\AdmissaoAso;
use App\Models\ClienteConfig;
use App\Models\FeedbackCurriculo;
use App\Models\Sistema;
use App\Models\TipoRecebeEmail;
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
        try {
            $listaDeEmprasaID = Sistema::listaEmpresas();
    
            foreach ($listaDeEmprasaID as $empresa_id) {
    
                $cliente = ClienteConfig::whereClienteId($empresa_id)->first();
                $periodo_vencimento = ClienteConfig::LISTA_VENCIMENTOS[$cliente->vencimento_aso];
    
                if (!empty($cliente)) {
                    $periodo_vencimento = 365 - preg_replace("/[^0-9]/", "", $periodo_vencimento);
                    $data = new DataHora();
                    $data->addDia($periodo_vencimento);
                    
                    $usuarios = User::whereEmpresaId($empresa_id)
                        ->select(['id', 'nome', 'login'])
                        ->whereAtivo(true)
                        ->whereHas('UserRecebeEmail', function ($q) {
                            $q->where('nome', TipoRecebeEmail::VENCIMENTO_ASO);
                            $q->where('ativo', true);
                        })
                        ->with(['UserRecebeEmail' => function ($q) {
                            $q->where('nome', TipoRecebeEmail::VENCIMENTO_ASO);
                            $q->where('ativo', true);
                        }]);
    
                      $data_ano = new DataHora();
                      $data_ano->addDia(365);
    
                        $AdmissoesAso = AdmissaoAso::whereAtivo(true)->whereEmpresaId($empresa_id)
                        ->where('data_vencimento', '>=', $data->dataInsert())
                        ->where('data_vencimento', '<=', $data_ano->dataInsert())
                        ->with('Admissao:id,feedback_id')
                        ->with(['Admissao.Feedback' => function ($q) use ($empresa_id) {
                            $q->withoutGlobalScopes()->select([
                                'id',
                                'curriculo_id',
                                'empresa_id'
                            ])->where('empresa_id', $empresa_id);
                        }, 'Admissao.Feedback.Curriculo' => function ($qu) {
                            $qu->withoutGlobalScopes()->select([
                                'id',
                                'nome',
                                'nascimento',
                                'rg'
                            ]);
                        }])
                        ->orderBy('data_vencimento')
                        ->get();
                    foreach ($usuarios as $usuario) {
                        $vencimentos = array();
                        $dados = array();
    
                        foreach ($AdmissoesAso as $vencimento) {
    
                            $dados['colaborador'] = $vencimento->Admissao->Feedback->Curriculo->nome;
                            $dados['data_vencimento'] = $vencimento->data_vencimento;
    
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
                        \Log::info("E-mail de Vencimento ASO enviado com sucesso - {$usuario['nome']} - {$empresa_id}");
    
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
            \Log::error($e->getFile() . " - " . $e->getMessage() . " - " . $e->getCode() . ' Verifica Saida Ferias');
        };
    }
}
