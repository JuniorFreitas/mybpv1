<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Services\AvaliacaoNoventaService;
use App\Jobs\GerarTokenAvaliacaoNoventaDiasJob;
use App\Models\AvaliacaoNoventaVencimento;
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
     * Exibe relatório de avaliações de 90 dias
     * Lista todas as avaliações e gera tokens se necessário
     */
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        // Busca avaliações vencidas ou vencendo (mesma lógica do comando)
        $avaliacoes = $this->avaliacaoService->buscarAvaliacoesVencendoOuVencidas(
            $empresaId,
            AvaliacaoNoventaService::DIAS_ANTECEDENCIA,
            true // incluir completas (2 avaliações) no relatório manual
        );

    // Monta vencimentos sem gerar tokens para não custar o carregamento do relatório
        $dataAtual = (new \MasterTag\DataHora())->dataCompleta();
    $vencimentos = $this->avaliacaoService->montarVencimentos($avaliacoes, $dataAtual, false);

        // Ordena por prioridade: VENCIDO > VENCE HOJE > A VENCER > COMPLETA
        $ordemStatus = [
            'VENCIDO' => 1,
            'VENCE HOJE' => 2,
            'A VENCER' => 3,
            'COMPLETA' => 4,
        ];

        $vencimentos = $vencimentos->sortBy(function ($vencimento) use ($ordemStatus) {
            $status = $vencimento['status'] ?? 'COMPLETA';
            $prioridade = $ordemStatus[$status] ?? 999;

            // Dentro de cada status, ordena por dias (mais atrasado/próximo primeiro)
            $dias = 0;
            if ($status === 'VENCIDO') {
                $dias = $vencimento['dias_atraso'] ?? 0; // Maior atraso primeiro
            } elseif ($status === 'A VENCER') {
                $dias = -($vencimento['dias_para_vencer'] ?? 0); // Menor dias para vencer primeiro (inverte sinal)
            }

            return [$prioridade, -$dias]; // Negativo para ordem decrescente de dias
        })->values();

        // Separa por status para facilitar visualização
        $vencimentosPorStatus = [
            'VENCIDO' => $vencimentos->where('status', 'VENCIDO'),
            'VENCE HOJE' => $vencimentos->where('status', 'VENCE HOJE'),
            'A VENCER' => $vencimentos->where('status', 'A VENCER'),
        ];

        // Contadores para resumo
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

        // Prepara listas únicas para os filtros
        $centrosCusto = $vencimentos->pluck('centro_custo')->unique()->filter()->sort()->values();
        $gestores = $vencimentos
            ->filter(function($v) { return !empty($v['gestor_nome']); })
            ->map(function($v) { return ['id' => $v['gestor_id'], 'nome' => $v['gestor_nome']]; })
            ->unique('id')
            ->sortBy('nome')
            ->values();
        $cargos = $vencimentos->pluck('cargo')->unique()->filter()->sort()->values();
        $funcoes = $vencimentos->pluck('funcao')->unique()->filter()->sort()->values();

        // Top 5 Gestores com mais pendências (VENCIDO, VENCE HOJE, A VENCER)
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
            'tokens_gerados' => $vencimentos->filter(fn($v) => !empty($v['token']))->count()
        ]);

        return view('g.relatorios.avaliacao90dias.index', [
            'vencimentos' => $vencimentos,
            'vencimentosPorStatus' => $vencimentosPorStatus,
            'resumo' => $resumo,
            'dataGeracao' => now()->format('d/m/Y H:i:s'),
            'centrosCusto' => $centrosCusto,
            'gestores' => $gestores,
            'cargos' => $cargos,
            'funcoes' => $funcoes,
            'topGestores' => $topGestores,
        ]);
    }

    /**
     * Dispara o comando de notificação para o usuário logado
     */
    public function exportar(Request $request)
    {
        $usuario = auth()->user();
        $empresaId = $usuario->empresa_id;
        $lockKey = "avaliacao90dias_lock_{$usuario->id}";

        // Tenta adquirir lock distribuído por 2 minutos (tempo estimado de processamento)
        $lock = \Cache::lock($lockKey, 120);

        try {
            // Tenta obter o lock
            if (!$lock->get()) {
                // Já existe uma exportação em andamento
                return response()->json([
                    'success' => false,
                    'message' => 'Já existe uma exportação em andamento. Aguarde a conclusão.',
                    'em_processamento' => true
                ], 429);
            }

            Log::info('Solicitação de exportação de avaliação 90 dias', [
                'usuario_id' => $usuario->id,
                'usuario_nome' => $usuario->nome,
                'usuario_email' => $usuario->login,
                'empresa_id' => $empresaId
            ]);

            // Armazena metadata do processamento
            \Cache::put("avaliacao90dias_meta_{$usuario->id}", [
                'iniciado_em' => now()->toDateTimeString(),
                'usuario_id' => $usuario->id,
                'status' => 'processando'
            ], now()->addMinutes(2));

            // Dispara o comando artisan em background usando queue
            \Artisan::queue('mybp:avaliacao90dias', [
                '--empresa_id' => $empresaId,
                '--usuario' => $usuario->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Relatório em processamento! Você receberá o arquivo por e-mail em alguns instantes.'
            ]);

        } catch (\Throwable $e) {
            // Libera o lock em caso de erro
            try {
                $lock->forceRelease();
            } catch (\Exception $releaseException) {
                Log::warning('Erro ao liberar lock', [
                    'error' => $releaseException->getMessage()
                ]);
            }

            \Cache::forget("avaliacao90dias_meta_" . auth()->id());

            Log::error('Erro ao exportar avaliação 90 dias', [
                'usuario_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao processar exportação. Tente novamente.'
            ], 500);
        }
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

        // Valida se pertence à empresa do usuário
        $existe = AvaliacaoNoventaVencimento::query()
            ->where('feedback_id', $feedbackId)
            ->whereHas('FeedbackCurriculo', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            })
            ->exists();

        if (!$existe) {
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

        // Valida que todos pertencem à empresa do usuário
        $validos = AvaliacaoNoventaVencimento::query()
            ->whereIn('feedback_id', $feedbackIds)
            ->whereHas('FeedbackCurriculo', function ($q) use ($empresaId) {
                $q->where('empresa_id', $empresaId);
            })
            ->pluck('feedback_id')
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
            $link = url('/avaliacao-90-dias/' . $vencimento->token_avaliacao);
        }

        return response()->json([
            'success' => true,
            'link' => $link,
        ]);
    }
}
