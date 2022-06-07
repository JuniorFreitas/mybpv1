<?php

namespace App\Jobs\Admissao\Historico\AvaliacaoNoventaVencimento;

use App\Mail\Admissao\Historico\AvaliacaoNoventaVencimento\AvaliacaoNoventaVencimentoMail;
use App\Mail\Movimentacao\FeriasPrevista\VencimentoMail;
use App\Models\AvaliacaoNoventaVencimento;
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

class AvaliacaoNoventaVencimentoJob implements ShouldQueue
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
            $agora = $agora->dataCompleta();

            $listaDeEmprasaID = Sistema::listaEmpresas();

            foreach ($listaDeEmprasaID as $empresa_id) {

                $vencimentoParaLembrar = AvaliacaoNoventaVencimento::whereHas('FeedbackCurriculo', function ($q) use ($empresa_id) {
                    $q->withoutGlobalScopes()->where('empresa_id', $empresa_id);
                })->with(['FeedbackCurriculo' => function ($q) use ($empresa_id) {
                    $q->withoutGlobalScopes()->where('empresa_id', $empresa_id);
                }, 'FeedbackCurriculo.Curriculo' => function ($qu) {
                    $qu->withoutGlobalScopes();
                }])->get();

                $usuarios = User::withoutGlobalScopes()->whereEmpresaId($empresa_id)
                    ->select(['id', 'nome', 'login'])
                    ->whereIn('tipo', ['Administrador', 'Suporte'])
                    ->whereHas('UserRecebeEmail', function ($q) {
                        $q->where('nome', 'Avaliação 90 Dias');
                        $q->where('ativo', true);
                    })
                    ->with(['UserRecebeEmail' => function ($q) {
                        $q->where('nome', 'Avaliação 90 Dias');
                        $q->where('ativo', true);
                    }]);

                foreach ($usuarios as $usuario) {
                    $vencimentos = array();
                    $dados = array();
                    foreach ($vencimentoParaLembrar as $vencimento) {

                        $dados['colaborador'] = $vencimento['FeedbackCurriculo']['Curriculo']->nome;
                        if ($vencimento['prazo_dez_inicial'] == $agora) {
                            $dados['prazo_vencido'] = $vencimento['prazo_dia_inicial'];
                        } elseif ($vencimento['prazo_cinco_inicial'] == $agora) {
                            $dados['prazo_vencido'] = $vencimento['prazo_dia_inicial'];
                        } elseif ($vencimento['prazo_dia_inicial'] == $agora) {
                            $dados['prazo_vencido'] = $vencimento['prazo_dia_inicial'];
                        } elseif ($vencimento['prazo_dez_final'] != null && $vencimento['prazo_dez_final'] == $agora) {
                            $dados['prazo_vencido'] = $vencimento['prazo_dia_final'];
                        } elseif ($vencimento['prazo_dez_final'] != null && $vencimento['prazo_cinco_final'] == $agora) {
                            $dados['prazo_vencido'] = $vencimento['prazo_dia_final'];
                        } elseif ($vencimento['prazo_dez_final'] != null && $vencimento['prazo_dia_final'] == $agora) {
                            $dados['prazo_vencido'] = $vencimento['prazo_dia_final'];

                        }
                        if (!empty($dados['prazo_vencido'])) {
                            array_push($vencimentos, $dados);
                        }
                    }

                    if (!empty($vencimentos)) {
                        \Mail::send(new AvaliacaoNoventaVencimentoMail([
                            'usuario' => $usuario,
                            'vencimentos' => $vencimentos,
                            'empresa_id' => $empresa_id,
                        ]));
                        \Log::info("E-mail de Avaliação enviado com sucesso - {$usuario['nome']} - {$empresa_id} horario: {$agora}");

                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error($e->getFile() . " - " . $e->getMessage() . " - " . $e->getCode() . ' Avaliacaao Noventa Vencimento');
        }
    }
}
