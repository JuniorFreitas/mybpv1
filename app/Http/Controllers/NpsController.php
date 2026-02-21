<?php

namespace App\Http\Controllers;

use App\Jobs\JobExportaNpsExcel;
use App\Models\Acessos;
use App\Models\NpsCiclo;
use App\Models\NpsPergunta;
use App\Models\NpsResposta;
use App\Models\NpsRespostaItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NpsController extends Controller
{
    /**
     * Garante que apenas a empresa de gerenciamento (ex.: 100) pode acessar.
     */
    private function autorizarGerenciamento(): void
    {
        $empresaId = (int) (auth()->user()->empresa_id ?? 0);
        $gerenciamentoId = (int) config('nps.empresa_id_gerenciamento', 100);
        if ($empresaId !== $gerenciamentoId) {
            abort(403, 'Acesso ao gerenciamento NPS não permitido para esta empresa.');
        }
    }

    /**
     * Tela de gerenciamento e resultados do NPS (apenas empresa configurada, ex.: 100).
     * Filtros: data_inicio, data_fim, empresa_id.
     *
     * @param Request $request
     * @return View|JsonResponse
     */
    public function gerenciamento(Request $request)
    {
        $this->autorizarGerenciamento();

        $dataInicio = null;
        $dataFim = null;
        if ($request->filled('data_inicio')) {
            try {
                $dataInicio = Carbon::createFromFormat('d/m/Y', $request->input('data_inicio'))->startOfDay();
            } catch (\Exception $e) {
                // ignora filtro se data inválida
            }
        }
        if ($request->filled('data_fim')) {
            try {
                $dataFim = Carbon::createFromFormat('d/m/Y', $request->input('data_fim'))->endOfDay();
            } catch (\Exception $e) {
                // ignora filtro se data inválida
            }
        }
        $empresaId = $request->filled('empresa_id') ? (int) $request->input('empresa_id') : null;
        $cicloId = $request->filled('ciclo_id') ? (int) $request->input('ciclo_id') : null;

        $queryRespostas = NpsResposta::query();
        if ($cicloId !== null) {
            $queryRespostas->where('nps_ciclo_id', $cicloId);
        }
        if ($dataInicio) {
            $queryRespostas->where('created_at', '>=', $dataInicio);
        }
        if ($dataFim) {
            $queryRespostas->where('created_at', '<=', $dataFim);
        }
        if ($empresaId !== null) {
            $queryRespostas->where('empresa_id', $empresaId);
        }
        $respostaIds = $queryRespostas->pluck('id');
        $totalRespostas = $respostaIds->count();

        $perguntas = NpsPergunta::orderBy('ordem')->get(['id', 'texto', 'ordem', 'ativo']);

        $itensQuery = $respostaIds->isNotEmpty()
            ? NpsRespostaItem::query()->whereIn('nps_resposta_id', $respostaIds)
            : NpsRespostaItem::query()->whereRaw('1 = 0');

        $resumoPorPergunta = $itensQuery->clone()
            ->selectRaw('nps_pergunta_id, COUNT(*) as total, AVG(nota) as media')
            ->groupBy('nps_pergunta_id')
            ->get()
            ->keyBy('nps_pergunta_id');

        $distribuicaoPorPergunta = $itensQuery->clone()
            ->selectRaw('nps_pergunta_id, nota, COUNT(*) as qtd')
            ->groupBy('nps_pergunta_id', 'nota')
            ->get()
            ->groupBy('nps_pergunta_id');

        $resumoGeral = [
            'total_respostas' => $totalRespostas,
            'por_pergunta' => $perguntas->map(function ($p) use ($resumoPorPergunta, $distribuicaoPorPergunta) {
                $r = $resumoPorPergunta->get($p->id);
                $totalPerg = $r ? (int) $r->total : 0;
                $dist = $distribuicaoPorPergunta->get($p->id, collect());
                $porNota = [];
                foreach ([1, 2, 3, 4, 5] as $nota) {
                    $item = $dist->where('nota', $nota)->first();
                    $qtd = $item ? (int) $item->qtd : 0;
                    $porNota[$nota] = [
                        'qtd' => $qtd,
                        'pct' => $totalPerg > 0 ? round(($qtd / $totalPerg) * 100, 1) : 0,
                    ];
                }
                return [
                    'id' => $p->id,
                    'texto' => $p->texto,
                    'ordem' => $p->ordem,
                    'ativo' => $p->ativo,
                    'total' => $totalPerg,
                    'media' => $r ? round((float) $r->media, 2) : 0,
                    'por_nota' => $porNota,
                ];
            })->values()->all(),
        ];

        $ultimasRespostas = NpsResposta::with(['User:id,nome,login', 'itens.npsPergunta:id,texto', 'npsCiclo:id,nome,data_inicio,data_fim'])
            ->whereIn('id', $respostaIds->take(200)->all())
            ->orderByDesc('created_at')
            ->limit(100)
            ->get();

        $empresasComResposta = User::query()
            ->whereIn('id', NpsResposta::select('empresa_id')->distinct()->pluck('empresa_id'))
            ->orderBy('nome')
            ->get(['id', 'nome'])
            ->mapWithKeys(function ($u) {
                return [$u->id => $u->nome ?: 'Empresa #' . $u->id];
            });

        $ciclos = NpsCiclo::orderByDesc('data_inicio')->get(['id', 'nome', 'data_inicio', 'data_fim', 'ativo']);
        $ciclosParaSelect = $ciclos->map(function ($c) {
            return [
                'id' => $c->id,
                'nome' => $c->label,
            ];
        })->values()->all();

        $filtros = [
            'data_inicio' => $request->input('data_inicio'),
            'data_fim' => $request->input('data_fim'),
            'empresa_id' => $request->input('empresa_id'),
            'ciclo_id' => $request->input('ciclo_id'),
        ];

        if ($request->wantsJson()) {
            $empresasParaSelect = collect($empresasComResposta)->map(function ($nome, $id) {
                return ['id' => $id, 'nome' => $nome];
            })->values()->all();

            $ultimasRespostasJson = $ultimasRespostas->map(function ($r) use ($empresasComResposta) {
                return [
                    'id' => $r->id,
                    'user_nome' => $r->User ? ($r->User->nome ?? $r->User->login) : '—',
                    'empresa_nome' => $empresasComResposta[$r->empresa_id] ?? '—',
                    'ciclo_nome' => $r->npsCiclo ? $r->npsCiclo->nome : '—',
                    'data' => $r->created_at ? $r->created_at->format('d/m/Y H:i') : '—',
                    'itens' => $r->itens->map(function ($i) {
                        return [
                            'nota' => $i->nota,
                            'texto' => $i->npsPergunta ? $i->npsPergunta->texto : '',
                        ];
                    })->values()->all(),
                ];
            })->values()->all();

            return response()->json([
                'filtros' => $filtros,
                'empresasParaSelect' => $empresasParaSelect,
                'ciclosParaSelect' => $ciclosParaSelect,
                'resumoGeral' => $resumoGeral,
                'ultimasRespostasJson' => $ultimasRespostasJson,
            ]);
        }

        return view('g.relatorios.nps.index', [
            'resumoGeral' => $resumoGeral,
            'ultimasRespostas' => $ultimasRespostas,
            'empresas' => $empresasComResposta,
            'ciclos' => $ciclos,
            'filtros' => $filtros,
        ]);
    }

    /**
     * Dispara a exportação do relatório NPS para Excel (job em background com chunks).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function export(Request $request): JsonResponse
    {
        $this->autorizarGerenciamento();

        $filtros = [
            'data_inicio' => $request->input('data_inicio'),
            'data_fim' => $request->input('data_fim'),
            'empresa_id' => $request->input('empresa_id'),
            'ciclo_id' => $request->input('ciclo_id'),
        ];

        $nomeArquivo = 'relatorio_nps_' . date('YmdHis') . '_' . rand(1000, 9999) . '.xlsx';

        JobExportaNpsExcel::dispatch(auth()->id(), $nomeArquivo, $filtros);

        return response()->json(['msg' => 'Estamos gerando seu arquivo Excel. Assim que finalizado você será notificado.']);
    }

    /**
     * Retorna se o modal NPS deve ser exibido e os dados (mensagens + perguntas).
     *
     * @return JsonResponse
     */
    public function deveExibir(): JsonResponse
    {
        if (!config('nps.habilitado')) {
            return response()->json(['mostrar' => false]);
        }

        $user = auth()->user();
        $empresaId = $user->empresa_id ?? null;
        $empresasExcluidas = config('nps.empresas_excluidas', []);

        if (in_array((int) $empresaId, $empresasExcluidas, true)) {
            return response()->json(['mostrar' => false]);
        }

        $minAcessos = (int) config('nps.min_acessos_ultimos_90_dias', 3);
        if ($minAcessos > 0) {
            $acessosUltimos90Dias = Acessos::where('user_id', $user->id)
                ->where('created_at', '>=', now()->subDays(90))
                ->count();
            if ($acessosUltimos90Dias < $minAcessos) {
                return response()->json(['mostrar' => false]);
            }
        }

        $cicloVigente = NpsCiclo::cicloVigente();
        if ($cicloVigente) {
            $jaRespondeuNoCiclo = NpsResposta::where('user_id', $user->id)
                ->where('nps_ciclo_id', $cicloVigente->id)
                ->exists();
            if ($jaRespondeuNoCiclo) {
                return response()->json(['mostrar' => false]);
            }
        } else {
            $dias = (int) config('nps.dias_entre_respostas', 90);
            $ultimaResposta = NpsResposta::where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->first();
            if ($ultimaResposta && $ultimaResposta->created_at->gte(now()->subDays($dias))) {
                return response()->json(['mostrar' => false]);
            }
        }

        $perguntas = NpsPergunta::ativasParaEmpresa($empresaId)
            ->get(['id', 'texto', 'ordem'])
            ->map(function ($p) {
                return [
                    'id' => $p->id,
                    'texto' => $p->texto,
                    'ordem' => $p->ordem,
                ];
            });

        if ($perguntas->isEmpty()) {
            return response()->json(['mostrar' => false]);
        }

        return response()->json([
            'mostrar' => true,
            'mensagens' => config('nps.mensagens'),
            'perguntas' => $perguntas->values()->all(),
        ]);
    }

    /**
     * Salva as respostas do NPS (uma sessão por usuário, vários itens por pergunta).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $empresaId = auth()->user()->empresa_id;
        $idsAtivos = NpsPergunta::ativasParaEmpresa($empresaId)->pluck('id')->all();

        if (empty($idsAtivos)) {
            return response()->json(['erro' => true, 'mensagem' => 'Nenhuma pergunta ativa para responder.'], 422);
        }

        $rules = [
            'respostas' => 'required|array|min:1',
            'respostas.*.nps_pergunta_id' => 'required|integer|in:' . implode(',', $idsAtivos),
            'respostas.*.nota' => 'required|integer|min:1|max:5',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['erro' => true, 'mensagem' => 'Dados inválidos.', 'erros' => $validator->errors()], 422);
        }

        try {
            DB::beginTransaction();

            $cicloVigente = NpsCiclo::cicloVigente();
            $resposta = NpsResposta::create([
                'user_id' => auth()->id(),
                'empresa_id' => $empresaId,
                'nps_ciclo_id' => $cicloVigente ? $cicloVigente->id : null,
            ]);

            foreach ($request->input('respostas') as $item) {
                NpsRespostaItem::create([
                    'nps_resposta_id' => $resposta->id,
                    'nps_pergunta_id' => (int) $item['nps_pergunta_id'],
                    'nota' => (int) $item['nota'],
                ]);
            }

            DB::commit();
            return response()->json(['sucesso' => true], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('NPS store: ' . $e->getMessage());
            return response()->json(['erro' => true, 'mensagem' => 'Erro ao salvar respostas.'], 500);
        }
    }

    /**
     * Lista ciclos para select (apenas empresa gerenciamento).
     *
     * @return JsonResponse
     */
    public function ciclos(): JsonResponse
    {
        $this->autorizarGerenciamento();

        $ciclos = NpsCiclo::orderByDesc('data_inicio')->get(['id', 'nome', 'data_inicio', 'data_fim', 'ativo']);
        $lista = $ciclos->map(function ($c) {
            return [
                'id' => $c->id,
                'nome' => $c->label,
                'data_inicio' => $c->data_inicio->format('d/m/Y'),
                'data_fim' => $c->data_fim->format('d/m/Y'),
                'ativo' => $c->ativo,
            ];
        })->values()->all();

        return response()->json(['ciclos' => $lista]);
    }

    /**
     * Cria um novo ciclo NPS (apenas empresa gerenciamento).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function storeCiclo(Request $request): JsonResponse
    {
        $this->autorizarGerenciamento();

        $rules = [
            'nome' => 'required|string|max:255',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
            'ativo' => 'boolean',
        ];
        $validator = \Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['erro' => true, 'mensagem' => 'Dados inválidos.', 'erros' => $validator->errors()], 422);
        }

        $ativo = $request->boolean('ativo', true);
        $dataInicio = Carbon::parse($request->input('data_inicio'))->startOfDay();
        $dataFim = Carbon::parse($request->input('data_fim'))->endOfDay();

        $ciclo = NpsCiclo::create([
            'nome' => $request->input('nome'),
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'ativo' => $ativo,
        ]);

        return response()->json([
            'sucesso' => true,
            'ciclo' => [
                'id' => $ciclo->id,
                'nome' => $ciclo->label,
            ],
        ], 201);
    }
}
