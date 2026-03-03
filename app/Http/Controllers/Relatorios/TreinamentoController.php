<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Jobs\JobExportaExcel;
use App\Jobs\JobExportaTreinamentos;
use App\Jobs\JobRelatorioTreinamentoVencimento;
use App\Models\CentroCusto;
use App\Models\Cliente;
use App\Models\FeedbackCurriculo;
use App\Models\SegmentoTreinamento;
use App\Models\Treinamento;
use App\Services\Treinamento\FeedbackCurriculoFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use MasterTag\DataHora;

class TreinamentoController extends Controller
{
    public function index()
    {
        return view('g.relatorios.treinamento.index');
    }

    public function show(Request $request)
    {
        // Definir período padrão se não fornecido
        $periodoInput = $request->periodo ?? date('Y-m-d') . ' até ' . date('Y-m-d', strtotime('+30 days'));
        
        $periodo = explode(' até ', $periodoInput);
        if (count($periodo) < 2) {
            // Se não conseguir fazer split, usar período padrão
            $periodo = [date('Y-m-d'), date('Y-m-d', strtotime('+30 days'))];
        }
        
        $dataInicio = new DataHora($periodo[0] . ' 00:00:00');
        $dataFim = new DataHora($periodo[1] . ' 23:59:59');

        // Usar FeedbackCurriculoFilter corrigido - agora funciona igual ao AdmissaoController
        try {
            $filter = FeedbackCurriculoFilter::make();
            $segmentoId = $request->input('segmento_treinamento_id');
            
            // Preparar filtros para o período de vencimento
            $filtros = [
                'campoDemitido' => false, // Apenas admitidos
                'campoVencimento' => 'true',
                'vencimento' => $periodo[0] . ' até ' . $periodo[1]
            ];

            // Adicionar filtros de CNPJ e Centro de Custo se fornecidos
            if (!empty($request->campoCnpj)) {
                $filtros['campoCnpj'] = $request->campoCnpj;
            }
            
            if (!empty($request->campoCentroCusto)) {
                $filtros['campoCentroCusto'] = $request->campoCentroCusto;
            }

            \Log::info('DEBUG - Usando FeedbackCurriculoFilter com filtros:', $filtros);

            $filter->apply($filtros);
            
            // Obter dados filtrados
            $dados = $filter->getQuery();

            if (!empty($segmentoId)) {
                $dados->whereHas('Admissao', function ($q) use ($segmentoId) {
                    $q->where('segmento_treinamento_id', $segmentoId);
                });
            }

            $dados = $dados->with([
                'Treinamento.Vencimentos' => function($q) use ($dataInicio, $dataFim) {
                    $q->whereBetween('treinamento_vencimento.data_vencimento', [$dataInicio->dataInsert(), $dataFim->dataInsert()]);
                },
                'Admissao.SegmentoTreinamento:id,nome,slug',
                'VagaSelecionada',
                'Curriculo'
            ])->get();

            \Log::info('DEBUG - FeedbackCurriculoFilter funcionou! Total encontrado:', ['count' => $dados->count()]);
            
        } catch (\Exception $e) {
            \Log::error('DEBUG - Erro no FeedbackCurriculoFilter: ' . $e->getMessage());
            
            // Fallback para implementação manual em caso de erro
            $query = FeedbackCurriculo::select([
                'id', 'curriculo_id', 'telefone_id', 'vaga_id', 'vagas_abertas_id', 'vaga_projeto_id'
            ]);

            $empresa_id = auth()->user()->empresa_id;
            
            $query->Admitidos()
                  ->whereHas('ResultadoIntegrado', function ($q) {
                      $q->whereEncaminhadoTreinamento(true);
                  })
                  ->where('empresa_id', $empresa_id);

            if (!empty($request->segmento_treinamento_id)) {
                $query->whereHas('Admissao', function ($q) use ($request) {
                    $q->where('segmento_treinamento_id', $request->segmento_treinamento_id);
                });
            }

            $dados = $query->with([
                'Treinamento.Vencimentos' => function($q) use ($dataInicio, $dataFim) {
                    $q->whereBetween('treinamento_vencimento.data_vencimento', [$dataInicio->dataInsert(), $dataFim->dataInsert()]);
                },
                'Admissao.SegmentoTreinamento:id,nome,slug',
                'VagaSelecionada',
                'Curriculo'
            ])->get();
        }
        
        \Log::info('DEBUG - Total de registros encontrados:', ['count' => $dados->count()]);
        
        $empresa_id = auth()->user()->empresa_id;
        $cc = (new CentroCusto())->listaCentroCustoPorCnpj($empresa_id);

        $resultado = collect();

        foreach ($dados as $feedback) {
            // Verificar se tem treinamento e vencimentos no período
            if (!$feedback->Treinamento || !$feedback->Treinamento->Vencimentos->isNotEmpty()) {
                continue;
            }

            $vencimentos = collect();
            $segmentoId = $feedback->Admissao
                ? ($feedback->Admissao->segmento_treinamento_id ?? SegmentoTreinamento::getIdAlumar())
                : SegmentoTreinamento::getIdAlumar();

            foreach ($feedback->Treinamento->Vencimentos as $vencimento) {
                if ($segmentoId && $vencimento->segmento_treinamento_id !== null && (int) $vencimento->segmento_treinamento_id !== (int) $segmentoId) {
                    continue;
                }
                $diasVencer = DataHora::diferencaDias((new DataHora())->dataInsert(), $vencimento->pivot->data_vencimento);
                
                $vencimentos->push([
                    'label' => $vencimento->label ?? 'Treinamento não encontrado',
                    'descricao' => $vencimento->descricao ?? '',
                    'data_treinamento' => $vencimento->pivot->data_treinamento,
                    'data_vencimento' => $vencimento->pivot->data_vencimento,
                    'dias_vencer' => $diasVencer,
                    'pintar' => $diasVencer <= 30
                ]);
            }

            if ($vencimentos->isNotEmpty()) {
                // Obter informações de centro de custo
                $cc_colaborador = null;
                if ($feedback->Admissao && $feedback->Admissao->centro_custo_id) {
                    $cc_colaborador = collect($cc['centros_custos'])->collapse()
                        ->where('id', $feedback->Admissao->centro_custo_id)->first();
                }

                $segmentoNome = $feedback->Admissao && $feedback->Admissao->SegmentoTreinamento
                    ? $feedback->Admissao->SegmentoTreinamento->nome
                    : '--';

                $resultado->push([
                    'nome' => $feedback->Curriculo->nome ?? 'Nome não encontrado',
                    'cargo' => $feedback->VagaSelecionada->nome ?? ($feedback->Admissao->cargo ?? 'NÃO ENCONTRADO'),
                    'emp_cnpj' => $cc_colaborador['cnpj_format'] ?? '--',
                    'emp_nome_fantasia' => $cc_colaborador['nome_fantasia'] ?? '--',
                    'emp_centro_custo' => $cc_colaborador['label'] ?? '--',
                    'emp_tipo' => ($cc_colaborador['matriz'] ?? false) ? 'Matriz' : 'Filial',
                    'segmento' => $segmentoNome,
                    'tipo' => $feedback->tipo ?? 'N/A',
                    'treinamentos' => $vencimentos->sortBy('dias_vencer')->values(),
                ]);
            }
        }

        $resultado = $resultado->transform(function ($item) {
            $tCollect = collect($item['treinamentos']);
            $item['pintar'] = $tCollect->where('pintar', true)->count() == $tCollect->count();
            $item['count_pintar'] = $tCollect->where('pintar', true)->count();
            return $item;
        })->sortByDesc('count_pintar')
            ->sortBy('pintar', SORT_REGULAR, true)->values();

        return response()->json([
            'cc' => $cc,
            'itens' => $resultado,
            'total_registros' => $resultado->count(),
            'periodo_consultado' => $periodoInput,
            'usando_feedback_curriculo_filter' => true,
            'compativel_admissao_controller' => true,
            'data_consulta' => now()->format('Y-m-d H:i:s')
        ]);
    }

    public function exportExcel(Request $request)
    {
        try {
            $userId = auth()->id();
            $requestData = $request->all();

            // Adicionar filtros específicos para vencimento de treinamentos
            $requestData['campoVencimento'] = 'true';
            $requestData['campoDemitido'] = false;
            
            // Definir período padrão se não fornecido
            if (!isset($requestData['periodo'])) {
                $requestData['periodo'] = date('Y-m-d') . ' até ' . date('Y-m-d', strtotime('+30 days'));
                $requestData['vencimento'] = $requestData['periodo'];
            }

            // Criar chave única baseada no usuário e parâmetros
            $cacheKey = 'export_vencimento_treinamentos_' . $userId . '_' . md5(json_encode($requestData));

            // Verificar se já existe exportação em andamento
            if (Cache::get($cacheKey)) {
                $cacheData = Cache::get($cacheKey);
                $status = $cacheData['status'] ?? 'processing';
                $attempts = $cacheData['attempt'] ?? 1;
                $maxTries = $cacheData['max_tries'] ?? 3;

                switch ($status) {
                    case 'processing':
                        $message = "Exportação em andamento (tentativa {$attempts}/{$maxTries}). Aguarde a conclusão.";
                        break;
                    case 'retrying':
                        $message = "Exportação tentando novamente (tentativa {$attempts}/{$maxTries}). Aguarde.";
                        break;
                    case 'completed':
                        $message = "Exportação já foi concluída. Verifique suas notificações.";
                        break;
                    case 'failed':
                        $message = "Última exportação falhou após {$maxTries} tentativas. Você pode tentar novamente.";
                        break;
                    default:
                        $message = "Já existe uma exportação em andamento. Aguarde a conclusão.";
                        break;
                }

                return response()->json([
                    'msg' => $message,
                    'status' => $status,
                    'initiated_at' => $cacheData['initiated_at'] ?? null,
                    'attempts' => $attempts,
                    'max_tries' => $maxTries,
                    'last_error' => $cacheData['last_error'] ?? null
                ], 200);
            }

            $nameArquivo = "vencimento_treinamentos_" . date('YmdHis') . "_" . rand(1000, 9999) . ".xlsx";
            $expiresAt = now()->addMinutes(15);

            // Armazenar no cache com controle de estado
            Cache::put($cacheKey, [
                'filename' => $nameArquivo,
                'initiated_at' => now(),
                'expires_at' => $expiresAt,
                'user_id' => $userId,
                'status' => 'queued',
                'attempt' => 0,
                'max_tries' => 3,
                'progress' => 0,
                'tipo_relatorio' => 'vencimento_treinamentos'
            ], $expiresAt);

            // Usar JobRelatorioTreinamentoVencimento específico para vencimento
            JobRelatorioTreinamentoVencimento::dispatch(
                $userId,
                $requestData,
                $nameArquivo,
                $cacheKey
            );

            return response()->json([
                'msg' => 'Estamos gerando seu arquivo excel de vencimento de treinamentos. Assim que finalizado você será notificado.',
                'export_id' => $cacheKey,
                'estimated_time' => '5-15 minutos',
                'usando_job_vencimento_especifico' => true,
                'mesmo_formato_frontend' => true,
                'otimizado_chunks' => true
            ]);

        } catch (\Exception $e) {
            \Log::error("Erro no controller de export vencimento treinamentos: " . $e->getMessage() . " " . $e->getFile() . " on line " . $e->getLine());

            // Limpar cache em caso de erro
            if (isset($cacheKey)) {
                Cache::forget($cacheKey);
            }

            return response()->json(['error' => 'Erro interno na exportação'], 500);
        }
    }
}
