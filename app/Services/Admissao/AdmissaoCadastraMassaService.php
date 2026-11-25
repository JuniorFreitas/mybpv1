<?php

namespace App\Services\Admissao;

use App\Models\Admissao;
use App\Models\AvaliacaoNoventaVencimento;
use App\Models\FeedbackCurriculo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use MasterTag\DataHora;
use Throwable;

/**
 * Cadastro em massa no processo de Admissão (TD-002 fatia 7).
 */
class AdmissaoCadastraMassaService
{
    /**
     * @param  array<string, mixed>  $input
     * @return array{status: int, body: array<string, mixed>|string}
     */
    public function executar(array $input, int $empresaId): array
    {
        $selecionados = $input['selecionados'] ?? null;
        if (!is_array($selecionados) || $selecionados === []) {
            return [
                'status' => 400,
                'body' => [
                    'msg' => 'Erro ao Salvar em Massa',
                    'erros' => ['selecionados' => ['Selecione ao menos um candidato.']],
                ],
            ];
        }

        $payloadBase = [
            'tipo_admissao' => $input['tipo_admissao'] ?? null,
            'prazo_experiencia' => $input['prazo_experiencia'] ?? null,
            'data_encerramento' => $input['data_encerramento'] ?? null,
            'documento_portaria' => $input['documento_portaria'] ?? null,
            'status_carteira_treinamento' => $input['status_carteira_treinamento'] ?? null,
            'padrao_treinamento_id' => app(AdmissaoPadraoTreinamentoResolver::class)
                ->resolver($input['padrao_treinamento_id'] ?? null),
            'status' => $input['status'] ?? null,
            'data_admissao' => $input['data_admissao'] ?? null,
            'data_entrega_area' => $input['data_entrega_area'] ?? null,
            'biometria' => $input['biometria'] ?? null,
        ];

        $tiposComEncerramento = ['TEMPORARIO', 'DETERMINADO'];
        $ultimoFeedback = null;

        try {
            DB::transaction(function () use ($selecionados, $payloadBase, $tiposComEncerramento, $empresaId, &$ultimoFeedback) {
                foreach ($selecionados as $feedbackId) {
                    $feedback = FeedbackCurriculo::withoutGlobalScopes()
                        ->whereKey((int) $feedbackId)
                        ->where('empresa_id', $empresaId)
                        ->whereNull('deleted_at')
                        ->first();

                    if (!$feedback) {
                        throw new \InvalidArgumentException('Feedback não encontrado para a empresa.');
                    }

                    $ultimoFeedback = $feedback;
                    $feedback->forceFill(['selecionado' => 'sim'])->save();

                    $payload = $payloadBase;

                    if ($feedback->Admissao) {
                        $feedback->Admissao->update($payload);
                    } else {
                        $feedback->Admissao()->create($payload);
                    }

                    $datas = [];

                    if ($payload['tipo_admissao'] == 'FIXO') {
                        $data = new DataHora($payload['data_admissao']);
                        switch ($payload['prazo_experiencia']) {
                            case '30+30':
                                $datas['prazo_dez_inicial'] = $data->addDia(20);
                                $datas['prazo_cinco_inicial'] = $data->addDia(5);
                                $datas['prazo_dia_inicial'] = $data->addDia(5);
                                $datas['prazo_dez_final'] = $data->addDia(20);
                                $datas['prazo_cinco_final'] = $data->addDia(5);
                                $datas['prazo_dia_final'] = $data->addDia(5);
                                break;
                            case '45+45':
                                $datas['prazo_dez_inicial'] = $data->addDia(35);
                                $datas['prazo_cinco_inicial'] = $data->addDia(5);
                                $datas['prazo_dia_inicial'] = $data->addDia(5);
                                $datas['prazo_dez_final'] = $data->addDia(35);
                                $datas['prazo_cinco_final'] = $data->addDia(5);
                                $datas['prazo_dia_final'] = $data->addDia(5);
                                break;
                            case '30+60':
                                $datas['prazo_dez_inicial'] = $data->addDia(20);
                                $datas['prazo_cinco_inicial'] = $data->addDia(5);
                                $datas['prazo_dia_inicial'] = $data->addDia(5);
                                $datas['prazo_dez_final'] = $data->addDia(50);
                                $datas['prazo_cinco_final'] = $data->addDia(5);
                                $datas['prazo_dia_final'] = $data->addDia(5);
                                break;
                            case '60+30':
                                $datas['prazo_dez_inicial'] = $data->addDia(50);
                                $datas['prazo_cinco_inicial'] = $data->addDia(5);
                                $datas['prazo_dia_inicial'] = $data->addDia(5);
                                $datas['prazo_dez_final'] = $data->addDia(20);
                                $datas['prazo_cinco_final'] = $data->addDia(5);
                                $datas['prazo_dia_final'] = $data->addDia(5);
                                break;
                        }
                        $payload['data_encerramento'] = null;
                        $avaliacao = AvaliacaoNoventaVencimento::whereFeedbackId($feedbackId)->first();
                        $datas['feedback_id'] = $feedbackId;
                        $avaliacao ? $avaliacao->update($datas) : AvaliacaoNoventaVencimento::create($datas);
                    }

                    if (in_array($payload['tipo_admissao'], $tiposComEncerramento, true)) {
                        $data = new DataHora($payload['data_encerramento']);
                        $datas['prazo_dez_inicial'] = $data->subtrairDia(5);
                        $datas['prazo_cinco_inicial'] = $data->subtrairDia(5);
                        $datas['prazo_dia_inicial'] = $payload['data_encerramento'];
                        $datas['prazo_dez_final'] = null;
                        $datas['prazo_cinco_final'] = null;
                        $datas['prazo_dia_final'] = null;
                        $avaliacao = AvaliacaoNoventaVencimento::whereFeedbackId($feedbackId)->first();
                        $datas['feedback_id'] = $feedbackId;
                        $avaliacao ? $avaliacao->update($datas) : AvaliacaoNoventaVencimento::create($datas);
                    }

                    if ($payload['tipo_admissao'] == 'INTERMITENTE') {
                        $avaliacao = AvaliacaoNoventaVencimento::whereFeedbackId($feedbackId)->first();
                        if ($avaliacao) {
                            $avaliacao->delete();
                        }
                    }
                }

                if (
                    $ultimoFeedback
                    && ($payloadBase['status'] ?? null) == Admissao::STATUS_ADMISSAO_ADMITIDO
                ) {
                    // Comportamento legado: sincroniza apenas o último feedback do lote.
                    User::SincronizaEmpresaFuncionario($ultimoFeedback->empresa_id, $ultimoFeedback->curriculo_id);
                    User::find($ultimoFeedback->curriculo_id)?->update(['tipo' => User::FUNCIONARIO]);
                }
            });

            return ['status' => 201, 'body' => []];
        } catch (Throwable $e) {
            Log::error('Erro ao cadastrar admissão em massa', [
                'empresa_id' => $empresaId,
                'arquivo' => $e->getFile(),
                'linha' => $e->getLine(),
                'codigo' => $e->getCode(),
                'erro' => $e->getMessage(),
            ]);

            return [
                'status' => 400,
                'body' => ['msg' => 'Houve um erro por favor tente novamente, Caso persista entre em contato com o suporte!'],
            ];
        }
    }
}
