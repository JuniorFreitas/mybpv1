<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Jobs\GerarTokenAvaliacaoNoventaDiasJob;
use App\Jobs\JobExportaAvaliacaoExperienciaExcel;
use App\Models\AvaliacaoNoventaVencimento;
use App\Services\AvaliacaoNoventaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AvaliacaoNoventaDiasController extends Controller
{
    protected $avaliacaoService;

    public function __construct(AvaliacaoNoventaService $avaliacaoService)
    {
        $this->avaliacaoService = $avaliacaoService;
    }

    /**
     * Retorna dados do relatório em JSON (para o componente Vue / front separado).
     * @deprecated Use atualizar() com paginação e filtros no servidor.
     */
    public function dados(Request $request)
    {
        $payload = $this->construirDadosRelatorio();
        $userId = auth()->id();
        $temPermissaoGestaoRh = in_array('privilegio_gestao_rh', auth()->user()->listaDeHabilidades());
        $ehGestorGlobal = collect($payload['vencimentos'])->contains(fn($v) => ($v['gestor_id'] ?? null) == $userId);

        return response()->json([
            'resumo' => $payload['resumo'],
            'vencimentos' => $payload['vencimentos'],
            'data_geracao' => $payload['dataGeracao'],
            'centros_custo' => $payload['centrosCusto'],
            'gestores' => $payload['gestores'],
            'cargos' => $payload['cargos'],
            'funcoes' => $payload['funcoes'],
            'user_can_gestao_rh' => $temPermissaoGestaoRh,
            'is_gestor_global' => $ehGestorGlobal,
        ]);
    }

    /**
     * Lista paginada com filtros no servidor (padrão do sistema: ControlePaginacao).
     * POST: page, porPagina|pages, status, nome, centroCusto, gestor, avaliacoes, cargo, funcao
     */
    public function atualizar(Request $request)
    {
        $page = max(1, (int) $request->input('page', 1));
        $perPage = max(1, (int) ($request->input('porPagina') ?: $request->input('pages', 20)));

        $payload = $this->construirDadosRelatorio();
        $vencimentos = $payload['vencimentos'];
        $vencimentos = $this->aplicarFiltros($vencimentos, $request);

        $total = $vencimentos->count();
        $ultima = $total > 0 ? (int) ceil($total / $perPage) : 1;
        $page = min($page, max(1, $ultima));

        $resumo = [
            'total' => $total,
            'vencidos' => $vencimentos->where('status', 'VENCIDO')->count(),
            'vence_hoje' => $vencimentos->where('status', 'VENCE HOJE')->count(),
            'a_vencer' => $vencimentos->where('status', 'A VENCER')->count(),
            'sem_avaliacao' => $vencimentos->where('qnt_avaliacoes', 0)->count(),
            'uma_avaliacao' => $vencimentos->where('qnt_avaliacoes', 1)->count(),
            'completas' => $vencimentos->filter(fn($v) => ($v['qnt_avaliacoes'] ?? 0) >= 2)->count(),
            'gestores_unicos' => $vencimentos->pluck('gestor_id')->filter()->unique()->count(),
            'sem_gestor' => $vencimentos->filter(fn($v) => empty($v['gestor_id']))->count(),
        ];

        $itens = $vencimentos->slice(($page - 1) * $perPage, $perPage)->values()->toArray();

        $userId = auth()->id();
        $temPermissaoGestaoRh = in_array('privilegio_gestao_rh', auth()->user()->listaDeHabilidades());
        $ehGestorGlobal = collect($itens)->contains(fn($v) => ($v['gestor_id'] ?? null) == $userId);

        return response()->json([
            'atual' => $page,
            'ultima' => $ultima,
            'total' => $total,
            'dados' => [
                'itens' => $itens,
                'resumo' => $resumo,
                'data_geracao' => $payload['dataGeracao'],
                'centros_custo' => $payload['centrosCusto'],
                'gestores' => $payload['gestores'],
                'cargos' => $payload['cargos'],
                'funcoes' => $payload['funcoes'],
                'user_can_gestao_rh' => $temPermissaoGestaoRh,
                'is_gestor_global' => $ehGestorGlobal,
            ],
        ]);
    }

    /**
     * Aplica filtros à coleção de vencimentos (mesma lógica do Vue).
     */
    private function aplicarFiltros($vencimentos, Request $request)
    {
        $status = strtoupper(trim((string) $request->input('status', '')));
        $nome = trim((string) $request->input('nome', ''));
        $centroCusto = trim((string) $request->input('centroCusto', ''));
        $gestor = trim((string) $request->input('gestor', ''));
        $avaliacoes = $request->input('avaliacoes');
        $cargo = trim((string) $request->input('cargo', ''));
        $funcao = trim((string) $request->input('funcao', ''));
        $definicaoContrato = trim((string) $request->input('definicaoContrato', ''));

        return $vencimentos->filter(function ($v) use ($status, $nome, $centroCusto, $gestor, $avaliacoes, $cargo, $funcao, $definicaoContrato) {
            if ($status !== '' && ($v['status'] ?? '') !== $status) {
                return false;
            }
            if ($nome !== '' && stripos($v['colaborador'] ?? '', $nome) === false) {
                return false;
            }
            if ($avaliacoes !== '' && $avaliacoes !== null && (string)($v['qnt_avaliacoes'] ?? '') !== (string) $avaliacoes) {
                return false;
            }
            if ($centroCusto !== '') {
                if ($centroCusto === '__SEM_CENTRO__' && (trim($v['centro_custo'] ?? '') !== '')) {
                    return false;
                }
                if ($centroCusto !== '__SEM_CENTRO__' && ($v['centro_custo'] ?? '') !== $centroCusto) {
                    return false;
                }
            }
            if ($gestor !== '') {
                if ($gestor === '__SEM_GESTOR__' && !empty(trim((string)($v['gestor_id'] ?? '')))) {
                    return false;
                }
                if ($gestor !== '__SEM_GESTOR__' && (string)($v['gestor_id'] ?? '') !== $gestor) {
                    return false;
                }
            }
            if ($cargo !== '' && ($v['cargo'] ?? '') !== $cargo) {
                return false;
            }
            if ($funcao !== '' && ($v['funcao'] ?? '') !== $funcao) {
                return false;
            }
            if ($definicaoContrato !== '' && ($v['definicao_contrato'] ?? '') !== $definicaoContrato) {
                return false;
            }
            return true;
        })->values();
    }

    /**
     * Exibe a página do relatório (front Vue consome API /dados).
     */
    public function index(Request $request)
    {
        return view('g.relatorios.avaliacao90dias.index');
    }

    /**
     * Monta os dados do relatório (vencimentos, resumo, filtros).
     */
    private function construirDadosRelatorio(): array
    {
        $empresaId = auth()->user()->empresa_id;

        $avaliacoes = $this->avaliacaoService->buscarAvaliacoesVencendoOuVencidas(
            $empresaId,
            AvaliacaoNoventaService::DIAS_ANTECEDENCIA,
            true
        );

        $dataAtual = (new \MasterTag\DataHora())->dataCompleta();
        $vencimentos = $this->avaliacaoService->montarVencimentos($avaliacoes, $dataAtual, false);

        $ordemStatus = [
            'VENCIDO' => 1,
            'VENCE HOJE' => 2,
            'A VENCER' => 3,
            'COMPLETA' => 4,
        ];

        $vencimentos = $vencimentos->sortBy(function ($vencimento) use ($ordemStatus) {
            $status = $vencimento['status'] ?? 'COMPLETA';
            $prioridade = $ordemStatus[$status] ?? 999;
            $dias = 0;
            if ($status === 'VENCIDO') {
                $dias = $vencimento['dias_atraso'] ?? 0;
            } elseif ($status === 'A VENCER') {
                $dias = -($vencimento['dias_para_vencer'] ?? 0);
            }
            return [$prioridade, -$dias];
        })->values();

        $vencimentosPorStatus = [
            'VENCIDO' => $vencimentos->where('status', 'VENCIDO'),
            'VENCE HOJE' => $vencimentos->where('status', 'VENCE HOJE'),
            'A VENCER' => $vencimentos->where('status', 'A VENCER'),
        ];

        $resumo = [
            'total' => $vencimentos->count(),
            'vencidos' => $vencimentosPorStatus['VENCIDO']->count(),
            'vence_hoje' => $vencimentosPorStatus['VENCE HOJE']->count(),
            'a_vencer' => $vencimentosPorStatus['A VENCER']->count(),
            'sem_avaliacao' => $vencimentos->where('qnt_avaliacoes', 0)->count(),
            'uma_avaliacao' => $vencimentos->where('qnt_avaliacoes', 1)->count(),
            'completas' => $vencimentos->filter(fn($v) => ($v['qnt_avaliacoes'] ?? 0) >= 2)->count(),
            'gestores_unicos' => $vencimentos->pluck('gestor_id')->filter()->unique()->count(),
            'sem_gestor' => $vencimentos->filter(fn($v) => empty($v['gestor_id']))->count(),
        ];

        $centrosCusto = $vencimentos->pluck('centro_custo')->unique()->filter()->sort()->values();
        $gestores = $vencimentos
            ->filter(function ($v) { return !empty($v['gestor_nome']); })
            ->map(function ($v) { return ['id' => $v['gestor_id'], 'nome' => $v['gestor_nome']]; })
            ->unique('id')
            ->sortBy('nome')
            ->values();
        $cargos = $vencimentos->pluck('cargo')->unique()->filter()->sort()->values();
        $funcoes = $vencimentos->pluck('funcao')->unique()->filter()->sort()->values();

        $pendentes = $vencimentos->filter(function ($v) {
            $status = $v['status'] ?? '';
            return in_array($status, ['VENCIDO', 'VENCE HOJE', 'A VENCER']) && !empty($v['gestor_id']);
        });

        $topGestores = $pendentes
            ->groupBy('gestor_id')
            ->map(function ($itens, $gestorId) {
                $first = $itens->first();
                return [
                    'gestor_id' => $gestorId,
                    'gestor_nome' => $first['gestor_nome'] ?? '—',
                    'gestor_login' => $first['gestor_login'] ?? null,
                    'total' => $itens->count(),
                    'vencidos' => $itens->where('status', 'VENCIDO')->count(),
                    'vence_hoje' => $itens->where('status', 'VENCE HOJE')->count(),
                    'a_vencer' => $itens->where('status', 'A VENCER')->count(),
                ];
            })
            ->values()
            ->sortByDesc('total')
            ->take(5)
            ->values();

        Log::info('Relatório de avaliação 90 dias gerado', [
            'usuario_id' => auth()->id(),
            'empresa_id' => $empresaId,
            'total_vencimentos' => $resumo['total'],
        ]);

        return [
            'vencimentos' => $vencimentos,
            'vencimentosPorStatus' => $vencimentosPorStatus,
            'resumo' => $resumo,
            'dataGeracao' => now()->format('d/m/Y H:i:s'),
            'centrosCusto' => $centrosCusto,
            'gestores' => $gestores,
            'cargos' => $cargos,
            'funcoes' => $funcoes,
            'topGestores' => $topGestores,
        ];
    }

    /**
     * Exportação Excel (padrão CIH: enfileira job, gera arquivo, grava em disco-exportacao, notifica).
     * Usa os mesmos filtros da tela; sem filtro exporta o total.
     */
    public function exportar(Request $request)
    {
        $filtros = [
            'status' => $request->input('status', ''),
            'nome' => $request->input('nome', ''),
            'centroCusto' => $request->input('centroCusto', ''),
            'gestor' => $request->input('gestor', ''),
            'avaliacoes' => $request->input('avaliacoes', ''),
            'cargo' => $request->input('cargo', ''),
            'funcao' => $request->input('funcao', ''),
            'definicao_contrato' => $request->input('definicaoContrato', ''),
        ];

        JobExportaAvaliacaoExperienciaExcel::dispatch(auth()->id(), $filtros);

        return response()->json([
            'msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.'
        ]);
    }

    /**
     * Verifica o status de uma exportação em andamento
     */
    public function statusExportacao(Request $request)
    {
        $usuario = auth()->user();
        $lockKey = "avaliacao90dias_lock_{$usuario->id}";

        // Verifica se o lock está ativo
        $lock = \Cache::lock($lockKey, 0);
        $emProcessamento = !$lock->get();

        if ($emProcessamento) {
            // Lock ativo, busca metadata
            $data = \Cache::get("avaliacao90dias_meta_{$usuario->id}", []);
            return response()->json([
                'em_processamento' => true,
                'iniciado_em' => $data['iniciado_em'] ?? null
            ]);
        } else {
            // Não está processando, libera o lock de teste
            $lock->release();
            return response()->json([
                'em_processamento' => false
            ]);
        }
    }

    /**
     * Gera token de forma assíncrona para um determinado feedback
     */
    public function gerarLink(Request $request, int $feedbackId)
    {
        $usuario = auth()->user();
        $empresaId = $usuario->empresa_id;

        // Valida se pertence à empresa e se ainda não está completo (2 avaliações)
        $vencimento = AvaliacaoNoventaVencimento::query()
            ->where('feedback_id', $feedbackId)
            ->whereHas('FeedbackCurriculo', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            })
            ->withCount('qntFeedback')
            ->first();

        if (!$vencimento) {
            \Log::warning('gerarLink: registro não encontrado para feedback', [
                'feedback_id' => $feedbackId,
                'empresa_id' => $empresaId,
                'usuario_id' => $usuario->id,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Registro não encontrado'
            ], 404);
        }

        if (($vencimento->qnt_feedback_count ?? 0) >= AvaliacaoNoventaService::MAX_AVALIACOES_PERMITIDAS) {
            return response()->json([
                'success' => false,
                'message' => 'Avaliação já completa. Não é possível gerar novo link.'
            ], 422);
        }

        \Log::info('gerarLink: enfileirando geração de token', [
            'feedback_id' => $feedbackId,
            'empresa_id' => $empresaId,
            'usuario_id' => $usuario->id,
        ]);
        GerarTokenAvaliacaoNoventaDiasJob::dispatch($feedbackId, $empresaId);

        return response()->json([
            'success' => true,
            'message' => 'Geração de link enfileirada'
        ], 202);
    }

    /**
     * Gera tokens em lote para múltiplos feedbacks
     */
    public function gerarLinksLote(Request $request)
    {
        $usuario = auth()->user();
        $empresaId = $usuario->empresa_id;

        $feedbackIds = $request->input('feedback_ids', []);
        if (empty($feedbackIds) || !is_array($feedbackIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum ID fornecido'
            ], 400);
        }

        // Valida que pertencem à empresa e que ainda não estão completos (2 avaliações)
        $validos = AvaliacaoNoventaVencimento::query()
            ->whereIn('feedback_id', $feedbackIds)
            ->whereHas('FeedbackCurriculo', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            })
            ->withCount('qntFeedback')
            ->get()
            ->filter(function ($v) {
                return ($v->qnt_feedback_count ?? 0) < AvaliacaoNoventaService::MAX_AVALIACOES_PERMITIDAS;
            })
            ->pluck('feedback_id')
            ->values()
            ->toArray();

        if (empty($validos)) {
            return response()->json([
                'success' => false,
                'message' => 'Nenhum registro válido encontrado'
            ], 404);
        }

        \Log::info('gerarLinksLote: enfileirando geração em lote', [
            'total_solicitados' => count($feedbackIds),
            'total_validos' => count($validos),
            'empresa_id' => $empresaId,
            'usuario_id' => $usuario->id,
        ]);

        // Dispara job para cada ID válido
        foreach ($validos as $feedbackId) {
            GerarTokenAvaliacaoNoventaDiasJob::dispatch($feedbackId, $empresaId);
        }

        return response()->json([
            'success' => true,
            'message' => count($validos) . ' link(s) enfileirado(s) para geração',
            'total' => count($validos)
        ], 202);
    }

    /**
     * Consulta o link (token) atual de um feedback
     */
    public function consultarLink(Request $request, int $feedbackId)
    {
        $usuario = auth()->user();
        $empresaId = $usuario->empresa_id;

        $vencimento = AvaliacaoNoventaVencimento::query()
            ->where('feedback_id', $feedbackId)
            ->whereHas('FeedbackCurriculo', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            })
            ->first(['token_avaliacao', 'token_expiracao', 'avaliacao_realizada']);

        if (!$vencimento) {
            return response()->json([
                'success' => false,
                'message' => 'Registro não encontrado'
            ], 404);
        }

        $link = null;
        if (!empty($vencimento->token_avaliacao) && !empty($vencimento->token_expiracao) && \Carbon\Carbon::parse($vencimento->token_expiracao)->isFuture() && !$vencimento->avaliacao_realizada) {
            $link = url('/avaliacao-de-experiencia/' . $vencimento->token_avaliacao);
        }

        return response()->json([
            'success' => true,
            'link' => $link,
        ]);
    }
}
