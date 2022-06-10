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
            $ClientesConfigs = ClienteConfig::get();

            foreach ($ClientesConfigs as $cliente_config) {
                $periodo_vencimento = ClienteConfig::LISTA_VENCIMENTOS[$cliente_config->vencimento_aso];
                $empresa_id = $cliente_config->cliente_id;

                $periodo_vencimento = preg_replace("/[^0-9]/", "", $periodo_vencimento);
                $data = new DataHora();
                $data->addDia($periodo_vencimento);

                $usuarios = User::whereEmpresaId($empresa_id)
                    ->select(['id', 'nome', 'login'])
                    ->whereAtivo(true)
                    ->whereHas('UserRecebeEmail', function ($q) {
                        $q->where('nome', TipoRecebeEmail::VENCIMENTO_ASO)->where('ativo', true);
                    })
                    ->with(['UserRecebeEmail' => function ($q) {
                        $q->where('nome', TipoRecebeEmail::VENCIMENTO_ASO)->where('ativo', true);
                    }])->get();

                foreach ($usuarios as $usuario) {
                    $AdmissoesAso = AdmissaoAso::whereAtivo(true)->whereEmpresaId($empresa_id)
                        ->whereHas('Admissao', function ($q) {
                            $q->withoutGlobalScopes()->Admitidos();
                        })
                        ->select(['id', 'empresa_id', 'admissao_id', 'data_aso', 'data_vencimento'])
                        ->where('data_vencimento', '>=', (new DataHora())->dataInsert())
                        ->where('data_vencimento', '<=', $data->dataInsert())
                        ->with('Admissao', function ($a) {
                            $a->select(['id', 'feedback_id'])
                                ->Admitidos()
                                ->with('Feedback', function ($F) {
                                    $F->withoutGlobalScopes()->select(['id', 'curriculo_id', 'empresa_id'])
                                        ->with('Curriculo', function ($C) {
                                            $C->withoutGlobalScopes()->select(['id', 'nome', 'nascimento', 'rg', 'orgao_expeditor']);
                                        });
                                });
                        })
                        ->orderBy('data_vencimento')
                        ->get();

                    $vencimentos = [];
                    foreach ($AdmissoesAso as $vencimento) {
                        $vencimentos[] = [
                            'colaborador' => $vencimento->Admissao->Feedback->Curriculo->nome,
                            'data_vencimento' => $vencimento->data_vencimento_formatada
                        ];
                    }

                    if (!empty($vencimentos)) {
                        \App\Jobs\JobMailVencimentoAso::dispatch([
                            'usuario' => $usuario,
                            'vencimentos' => $vencimentos,
                            'empresa_id' => $empresa_id,
                        ])->delay(now()->addSeconds(5));
                        \Log::info("E-mail de Vencimento ASO enviado com sucesso - {$usuario['nome']} - {$empresa_id}");
                    }
                }

            }
        } catch (\Exception $e) {
            Log::debug("Erro ao processar Vencimento ASO - {$usuario['nome']} - {$empresa_id}");
        }
    }
}
