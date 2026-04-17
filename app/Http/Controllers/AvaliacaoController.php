<?php

namespace App\Http\Controllers;

use App\Jobs\JobExportaAvaliacoesCsv;
use App\Models\Avaliacao;
use App\Models\AvaliacaoAvaliadoresTipos;
use App\Models\AvaliacaoFeedback;
use App\Models\AvaliacaoResposta;
use App\Models\AvaliacaoResultado;
use App\Models\AvaliacaoTipo;
use App\Models\AvaliacaoTopico;
use App\Models\FeedbackCurriculo;
use App\Models\Sistema;
use App\Models\User;
use App\Rules\TenantUniqueRules;
use App\Services\Avaliacoes\AvaliacaoNotificacaoService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class AvaliacaoController extends Controller
{

    private function avaliacaoPermiteResponder(AvaliacaoFeedback $avaliacaoFeedback): bool
    {
        $avaliacao = $avaliacaoFeedback->avaliacao;

        if (!$avaliacao || $avaliacao->status !== Avaliacao::STATUS_ABERTA) {
            return false;
        }

        if (empty($avaliacao->getRawOriginal('data_fim_prazo')) && empty($avaliacao->data_fim_prazo)) {
            return true;
        }

        $dataFimPrazo = $avaliacao->getRawOriginal('data_fim_prazo') ?: $avaliacao->data_fim_prazo;

        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dataFimPrazo)) {
                $prazo = \Carbon\Carbon::createFromFormat('d/m/Y', $dataFimPrazo)->endOfDay();
            } else {
                $prazo = \Carbon\Carbon::parse($dataFimPrazo)->endOfDay();
            }
        } catch (\Throwable $e) {
            return false;
        }

        return now()->lte($prazo);
    }

    protected function temPrivilegioGestaoRh(): bool
    {
        return (bool)in_array('privilegio_gestao_rh', auth()->user()->listaDeHabilidades());
    }

    public function index(Request $request)
    {
        return view('g.cadastros.avaliacoes.avaliacao.index');
    }

    public function indexPj(Request $request)
    {
        return view('g.cadastros.avaliacoes-pj.avaliacao.index');
    }

    public function store(Request $request)
    {
        $this->authorize('cadastro_avaliacao_insert');
        $dados = $request->input();
        $dados['mostrar_notas_avaliador_final'] = (bool)($dados['mostrar_notas_avaliador_final'] ?? false);
        $titulo = $dados['titulo'];

        $arrayValidacao = [
            'titulo' => [
                function ($attribute, $value, $fail) use ($dados) {
                    if (strlen($value) <= 3) {
                        $fail('Informe uma título maior que 3 caracteres.');
                    }
                },
                'required',
                new TenantUniqueRules('avaliacoes', $request->segment(5))
            ],
            'avaliacao_tipo_id' => [function ($attribute, $value, $fail) use ($dados) {
                $avaliacao_tipo_id = $dados['avaliacao_tipo_id'];
                $avaliacaotipo = AvaliacaoTipo::whereId($avaliacao_tipo_id)->first();
                if (!$avaliacaotipo) {
                    $fail('Verificar o tipo de avaliação');
                }
            }],
            'data_inicio_prazo' => [function ($attribute, $value, $fail) use ($dados) {
                $datainicio = $dados['data_inicio_prazo'];
                $dataencerramento = $dados['data_fim_prazo'];

                $diff_dias = DataHora::diferencaDias($datainicio, $dataencerramento);

                if ($diff_dias < 0) {
                    $fail('Data Fim precisa ser maior que a Data início');
                }
            }],
        ];
        $dadosValidados = \Validator::make($dados, $arrayValidacao);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar a avaliação',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            Avaliacao::create($dados);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE AVALIAÇÃO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            Sistema::LogFormatado($dados);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function edit(Avaliacao $avaliacao)
    {
        $this->authorize('cadastro_avaliacao_update');
        $avaliacao->fluxo = is_null($avaliacao->fluxo) ? [] : $avaliacao->fluxo;
        return $avaliacao;
    }

    public function show(Avaliacao $avaliacao)
    {

    }

    public function update(Request $request, Avaliacao $avaliacao)
    {
        $this->authorize('cadastro_avaliacao_update');
        $dados = $request->input();
        $dados['mostrar_notas_avaliador_final'] = (bool)($dados['mostrar_notas_avaliador_final'] ?? false);

        $arrayValidacao = [
            'titulo' => [
                function ($attribute, $value, $fail) use ($dados) {
                    if (strlen($value) <= 3) {
                        $fail('Informe uma título maior que 3 caracteres.');
                    }
                },
                'required',
                new TenantUniqueRules('avaliacoes', $request->segment(5))
            ],
            'avaliacao_tipo_id' => [function ($attribute, $value, $fail) use ($dados) {
                $avaliacao_tipo_id = $dados['avaliacao_tipo_id'];
                $avaliacaotipo = AvaliacaoTipo::whereId($avaliacao_tipo_id)->first();
                if (!$avaliacaotipo) {
                    $fail('Verificar o tipo de avaliação');
                }
            }],
            'data_inicio_prazo' => [function ($attribute, $value, $fail) use ($dados) {
                $datainicio = $dados['data_inicio_prazo'];
                $dataencerramento = $dados['data_fim_prazo'];

                $diff_dias = DataHora::diferencaDias($datainicio, $dataencerramento);

                if ($diff_dias < 0) {
                    $fail('Data Fim precisa ser maior que a Data início');
                }
            }],
        ];
        $dadosValidados = \Validator::make($dados, $arrayValidacao);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao editar a avaliação',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $avaliacao->update($dados);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error UPDATE AVALIAÇÃO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            Sistema::LogFormatado($dados);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filtro(Request $request)
    {

        $resultado = Avaliacao::with('AvaliacaoTipo')
            ->orderBy('data_inicio_prazo');
        if ($request->filled('campoBusca')) {
            $resultado->where(function ($query) use ($request) {
                $query->where('titulo', 'like', "%$request->campoBusca%")
                    ->orWhere('id', $request->campoBusca);
            });
        }
        if ($request->filled('ano_avaliacao')) {
            $resultado->where("ano_avaliacao", $request->ano_avaliacao);
        }

        if ($request->filled('tipo_avaliacao')) {
            $resultado->where("avaliacao_tipo_id", $request->tipo_avaliacao);
        }

        if ($request->filled('status')) {
            $resultado->where("status", $request->status);
        }

        if ($request->filled('tipo_pj')) {
            $resultado->where('tipo_pj', $request->tipo_pj);
        }


        return $resultado;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function atualizar(Request $request)
    {
        $this->authorize('cadastro_avaliacao');
        $resultado = $this->filtro($request)->paginate($request->porPag ?: 20);
        $avaliacoes_tipos = AvaliacaoTipo::whereAtivo(true)->where('tipo_pj', $request->tipo_pj)->get();
        $lista_tipos_avaliadores = AvaliacaoAvaliadoresTipos::whereAtivo(true)->where('tipo_pj', $request->tipo_pj)->get();

        $listaAvaliacaoPorAno = (new Avaliacao())->listaTodasAvaliacoesAgrupadaAno(auth()->user()->empresa_id);

        //o filtro da listaAvaliacaoPorAno deve pecorrer a collect $listaAvaliacaoPorAno e filtrar os anos que contem o tipo de pj
        $filtroListaAvaliacaoPorAnoComTipoPj = collect($listaAvaliacaoPorAno)->map(function ($avaliacoes, $ano) use ($request) {
            return collect($avaliacoes)->filter(function ($avaliacao) use ($request) {
                return $avaliacao->tipo_pj === $request->tipo_pj;
            })->values();
        })->filter(function ($avaliacoes) {
            return $avaliacoes->isNotEmpty();
        });


//        dd($filtroListaAvaliacaoPorAnoComTipoPj);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'avaliacoes_tipos' => $avaliacoes_tipos,
                'lista_tipos_avaliadores' => $lista_tipos_avaliadores,
                'lista_status' => Avaliacao::LISTA_STATUS,
                'lista_avaliacoes_por_ano' => $filtroListaAvaliacaoPorAnoComTipoPj
            ]
        ]);
    }

    public function export(Request $request)
    {
        $this->authorize('cadastro_avaliacao');
        
        $nameArquivo = "avaliacoes_" . rand(1000, 9999) . "_" . date('YmdHis') . ".csv";
        $filtros = $request->all();
        
        \Log::info('Exportando avaliações - Filtros: ' . json_encode($filtros));
        
        JobExportaAvaliacoesCsv::dispatch(
            auth()->id(),
            "Cadastro - Avaliações",
            $nameArquivo,
            $filtros
        );

        return response()->json(['msg' => 'Estamos gerando seu arquivo CSV, assim que finalizado você será notificado.']);
    }

    public function ativaDesativa(Request $request)
    {
        $this->authorize('cadastro_avaliacao_active');

        $avaliacao = Avaliacao::find($request->id);
        $avaliacao->ativo = !$avaliacao->ativo;
        $avaliacao->save();
        $avaliacao->refresh();
        return response()->json(['ativo' => $avaliacao->ativo], 201);
    }


    public function atualizarAvaliar(Request $request)
    {
        $this->authorize('avaliacoes_listar');
        $queryFiltrada = $this->filtroAvaliar($request);
        $porPagina = (int)($request->porPag ?: 20);
        $paginaAtual = LengthAwarePaginator::resolveCurrentPage();
        $avaliacoes_tipos = AvaliacaoTipo::whereAtivo(true)->get();
        $lista_avaliacoes = Avaliacao::whereAtivo(true)->orderBy('titulo')->get();

        $requestOpcoesFiltro = $request->duplicate();
        $requestOpcoesFiltro->merge([
            'campoAvaliador' => null,
            'campoColaborador' => null,
            'campoComo' => null,
        ]);

        $opcoesFiltroFeedbacks = $this->filtroAvaliar($requestOpcoesFiltro)->get();

        if ($request->filled('campoLegenda')) {
            $feedbacksFiltrados = $this->filtrarFeedbacksPorLegenda(
                $this->decorarFeedbacks((clone $queryFiltrada)->get()),
                $request->campoLegenda
            )->values();

            $resultado = new LengthAwarePaginator(
                $feedbacksFiltrados->forPage($paginaAtual, $porPagina)->values(),
                $feedbacksFiltrados->count(),
                $porPagina,
                $paginaAtual,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $resultado = (clone $queryFiltrada)->paginate($porPagina);
            $resultado->setCollection($this->decorarFeedbacks(collect($resultado->items())));
        }

        $gruposColaboradores = collect($resultado->items())
            ->map(fn($item) => [
                'avaliacao_id' => $item->avaliacao_id,
                'funcionario_id' => $item->funcionario_id,
            ])
            ->unique(fn($item) => $item['avaliacao_id'] . '-' . $item['funcionario_id'])
            ->values();

        $feedbacksFluxoCompleto = collect();

        if ($gruposColaboradores->isNotEmpty()) {
            $feedbacksFluxoCompleto = AvaliacaoFeedback::with('Avaliacao.AvaliacaoTipo', 'TipoAvaliador', 'Funcionario:id,nome,login,temp,ativo,deleted_at', 'Avaliador:id,nome,login')
                ->whereHas('Funcionario', function ($query) {
                    $query->ativoNaoExcluido();
                })->whereHas('Avaliador', function ($query) {
                    $query->ativoNaoExcluido();
                })
                ->where(function ($query) use ($gruposColaboradores) {
                    foreach ($gruposColaboradores as $grupo) {
                        $query->orWhere(function ($subQuery) use ($grupo) {
                            $subQuery->where('avaliacao_id', $grupo['avaliacao_id'])
                                ->where('funcionario_id', $grupo['funcionario_id']);
                        });
                    }
                })
                ->get();
        }

        $avaliacoesFeedbacks = collect($resultado->items());
        $feedbacksFluxoCompleto = $this->decorarFeedbacks($feedbacksFluxoCompleto);

        $avaliacoes_ano = (new Avaliacao())->listaTodasAvaliacoesAgrupadaAno(auth()->user()->empresa_id);

        $listaAvaliadores = $opcoesFiltroFeedbacks
            ->filter(fn($item) => $item->Avaliador)
            ->map(fn($item) => [
                'id' => $item->Avaliador->id,
                'nome' => $item->Avaliador->nome,
            ])
            ->unique('id')
            ->sortBy('nome', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $listaColaboradores = $opcoesFiltroFeedbacks
            ->filter(fn($item) => $item->Funcionario)
            ->map(fn($item) => [
                'id' => $item->Funcionario->id,
                'nome' => $item->Funcionario->nome,
            ])
            ->unique('id')
            ->sortBy('nome', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $listaComo = $opcoesFiltroFeedbacks
            ->map(function ($item) {
                if ($item->origem_feedback === AvaliacaoFeedback::ORIGEM_FUNCIONARIO && !$item->principal) {
                    return [
                        'value' => 'autoavaliacao',
                        'label' => 'Autoavaliação',
                    ];
                }

                if (!$item->TipoAvaliador) {
                    return null;
                }

                $label = trim($item->TipoAvaliador->label ?? '');
                if ($label === '') {
                    return null;
                }

                return [
                    'value' => $item->principal ? "tipo:{$item->TipoAvaliador->id}:principal" : "tipo:{$item->TipoAvaliador->id}",
                    'label' => $item->principal && !str_contains($label, '(Avaliador Final)') ? "{$label} (Avaliador Final)" : $label,
                ];
            })
            ->filter()
            ->unique('value')
            ->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $avaliacoesFeedbacks,
                'itens_fluxo_completo' => $feedbacksFluxoCompleto,
                'avaliacoes_tipos' => $avaliacoes_tipos,
                'lista_avaliacoes' => $lista_avaliacoes,
                'lista_status' => Avaliacao::LISTA_STATUS,
                'tem_privilegio_gestao_rh' => $this->temPrivilegioGestaoRh(),
                'lista_avaliacoes_por_ano' => $avaliacoes_ano,
                'lista_avaliadores' => $listaAvaliadores,
                'lista_colaboradores' => $listaColaboradores,
                'lista_como' => $listaComo,
            ]
        ]);
    }

    private function decorarFeedbacks($feedbacks)
    {
        return collect($feedbacks)->transform(function ($item) {
            $avaliacaoFeedbackFunc = AvaliacaoFeedback::whereAvaliacaoId($item->avaliacao_id)->whereFuncionarioId($item->funcionario_id);
            $avaliacaoPar = clone $avaliacaoFeedbackFunc;
            $avaliacaoPar = $avaliacaoPar->where('origem_feedback', AvaliacaoFeedback::ORIGEM_AVALIADOR)->where('principal', false);
            $totalAvaliacaoPar = $avaliacaoPar->count();
            $totalAvaliacaoParConcluida = $avaliacaoPar->where('status', AvaliacaoFeedback::STATUS_CONCLUIDA)->count();

            $item->total_avaliacoes = $avaliacaoFeedbackFunc->count();
            $totalAvaliacoesFuncConcluidas = $avaliacaoFeedbackFunc->whereIn('status', [AvaliacaoFeedback::STATUS_CONCLUIDA]);
            $item->total_avaliacoes_concluidas = $totalAvaliacoesFuncConcluidas->count();
            $item->fez_auto_avaliacao = is_null($item->avaliacao_tipo_id) && $item->status == AvaliacaoFeedback::STATUS_CONCLUIDA || $item->status == AvaliacaoFeedback::STATUS_FINAL || $totalAvaliacoesFuncConcluidas->count() > 0;
            $item->fazer_avaliacao_final = $item->principal && $item->total_avaliacoes_concluidas === $item->total_avaliacoes;

            $item->pendente_autoavaliacao = $item->avaliador_id == $item->funcionario_id && !$item->fez_auto_avaliacao;
            $item->pendente_autoavaliacao_colaborador = $item->avaliador_id != $item->funcionario_id && $item->status == 'Pendente' && !$item->fez_auto_avaliacao;
            $item->pendente_avaliacao_par = $totalAvaliacaoPar != $totalAvaliacaoParConcluida;
            $item->pendente_avaliacao_gestor = $item->total_avaliacoes - $item->total_avaliacoes_concluidas;
            $item->token = \Crypt::encrypt($item->id);
            $item->titulo_avaliacao = $item->Avaliacao?->titulo ?? 'Não informado';
            $item->tipo_avaliacao = $item->Avaliacao?->AvaliacaoTipo?->nome ?? 'Não informado';

            $item->fluxo = Avaliacao::fluxoAvaliacao($item->avaliacao_id);

            if ($item->Avaliacao && !$item->Avaliacao->auto_avaliacao) {
                $item->total_avaliacoes = $avaliacaoFeedbackFunc->count();
                $item->pendente_avaliacao_gestor = $item->total_avaliacoes - $item->total_avaliacoes_concluidas;
                $item->fazer_avaliacao_final = $item->principal && $item->total_avaliacoes_concluidas === $item->total_avaliacoes && $item->status != 'Finalizada';
            }

            $avaliacaoFeedbackPrincipal = AvaliacaoFeedback::whereAvaliacaoId($item->avaliacao_id)
                ->whereFuncionarioId($item->funcionario_id)
                ->wherePrincipal(true)
                ->first();

            $item->pdi_cadastrado = false;

            if ($avaliacaoFeedbackPrincipal) {
                $item->pdi_cadastrado = AvaliacaoResultado::where('avaliacao_feedback_id', $avaliacaoFeedbackPrincipal->id)
                    ->where('gestor_id', $avaliacaoFeedbackPrincipal->avaliador_id)
                    ->exists();
            }

            return $item;
        });
    }

    private function filtrarFeedbacksPorLegenda($feedbacks, string $legenda)
    {
        return collect($feedbacks)->filter(function ($item) use ($legenda) {
            return match ($legenda) {
                'autoavaliacao_pendente' => (bool)$item->pendente_autoavaliacao,
                'autoavaliacao_realizada' => $item->origem_feedback === AvaliacaoFeedback::ORIGEM_FUNCIONARIO && !$item->principal && $item->status !== AvaliacaoFeedback::STATUS_AGUARDANDO,
                'avaliacao_par_pendente' => (bool)$item->pendente_avaliacao_par,
                'avaliacao_par_realizada' => $item->origem_feedback === AvaliacaoFeedback::ORIGEM_AVALIADOR && !$item->principal && $item->status !== AvaliacaoFeedback::STATUS_AGUARDANDO,
                'avaliacao_gestor_pendente' => (bool)$item->pendente_avaliacao_gestor,
                'avaliacao_gestor_realizada' => (bool)!$item->pendente_avaliacao_gestor && (bool)$item->principal,
                'fluxo_concluido' => $item->status === AvaliacaoFeedback::STATUS_FINAL,
                'acompanhamento_plano_acao' => $item->status === AvaliacaoFeedback::STATUS_FINAL && (bool)$item->pdi_cadastrado,
                default => true,
            };
        });
    }

    public function getListaAvaliacoes(Request $request)
    {
//        $this->authorize('cadastro_avaliacao');
        $resultado = Avaliacao::whereAtivo(true)
            ->whereHas('AvaliacaoFeedbacks', function ($query) {
                if (!$this->temPrivilegioGestaoRh()) {
                    $query->where('funcionario_id', auth()->user()->id)
                        ->orWhere('avaliador_id', auth()->user()->id);
                }
            })
            ->orderBy('titulo')->get();

        return response()->json([
            'lista_avaliacoes' => $resultado,
            'lista_anos' => $resultado->groupBy('ano_avaliacao')->keys(),
        ]);
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\LaravelIdea\Helper\App\Models\_IH_AvaliacaoFeedback_QB
     */
    private function filtroAvaliar(Request $request)
    {

//        $resultado = AvaliacaoFeedback::with('Avaliacao.AvaliacaoTipo', 'Funcionario:id,nome,login,temp,ativo,deleted_at', 'Avaliador:id,nome,login')
//            ->whereHas('Funcionario', function ($query) {
//                $query->ativoNaoExcluido();
//            })->whereHas('Avaliador', function ($query) {
//                $query->ativoNaoExcluido();
//            })->whereAvaliadorId(auth()->user()->id);

        $resultado = AvaliacaoFeedback::with('Avaliacao.AvaliacaoTipo', 'TipoAvaliador', 'Funcionario:id,nome,login,temp,ativo,deleted_at', 'Avaliador:id,nome,login')
            ->whereHas('Funcionario', function ($query) {
                $query->ativoNaoExcluido();
            })->whereHas('Avaliador', function ($query) {
                $query->ativoNaoExcluido();
            });
        if (!$this->temPrivilegioGestaoRh()) {
            $resultado->whereAvaliadorId(auth()->user()->id);
        }

        $resultado->whereHas('Avaliacao', function ($query) use ($request) {
            $query->whereIn('status', [Avaliacao::STATUS_ABERTA, Avaliacao::STATUS_ENCERRADA])
                ->whereAtivo(true);
        });

        if ($request->filled('campoBusca')) {
            $resultado->where(function ($query) use ($request) {
                $query->where('titulo', 'like', "%$request->campoBusca%")
                    ->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoAvaliador')) {
            $resultado->whereAvaliadorId($request->campoAvaliador);
        }

        if ($request->filled('campoColaborador')) {
            $resultado->whereFuncionarioId($request->campoColaborador);
        }

        if ($request->filled('campoComo')) {
            if ($request->campoComo === 'autoavaliacao') {
                $resultado->where('origem_feedback', AvaliacaoFeedback::ORIGEM_FUNCIONARIO)
                    ->where('principal', false);
            } elseif (preg_match('/^tipo:(\d+)(:principal)?$/', $request->campoComo, $matches)) {
                $resultado->where('avaliacao_tipo_id', $matches[1]);

                if (!empty($matches[2])) {
                    $resultado->where('principal', true);
                } else {
                    $resultado->where('principal', false);
                }
            }
        }

        $Avaliacao = Avaliacao::select(['id', 'auto_avaliacao'])->whereAtivo(true)->orderBy('titulo')->limit(1)->first();

        if ($request->filled('campoAvaliacao')) {
            $Avaliacao = Avaliacao::select(['id', 'auto_avaliacao'])->whereId($request->campoAvaliacao)->first();

            if ($request->filled('campoStatus')) {
                $resultado->whereStatus($request->campoStatus);
            }


//            if ($request->filled('ano_avaliacao')) {
//                $resultado->whereHas('Avaliacao', function ($query) use ($request) {
//                    $query->where("ano_avaliacao", $request->ano_avaliacao);
//                });
//            }

//            if ($request->filled('tipo_avaliacao')) {
//                $resultado->whereHas('Avaliacao', function ($query) use ($request) {
//                    $query->where("avaliacao_tipo_id", $request->tipo_avaliacao);
//                });
//            }

//            if ($request->filled('status')) {
//                $query->where("status", $request->status);
//            }

            if ($Avaliacao && !$Avaliacao->auto_avaliacao) {
                $resultado->where('principal', true);
            }
            $resultado->where('avaliacao_id', $request->campoAvaliacao);
        } else {
            if ($Avaliacao) {
                if (!$Avaliacao->auto_avaliacao) {
                    $resultado->where('principal', true);
                }
                $resultado->where('avaliacao_id', $Avaliacao->id);
            } else {
                $resultado->whereRaw('1 = 0');
            }
        }

        return $resultado;
    }

    public function avaliarIndex(Request $request)
    {
        return view('g.cadastros.avaliacoes.avaliar.index');
    }

    public function notificarPendente(AvaliacaoFeedback $avaliacaoFeedback, AvaliacaoNotificacaoService $avaliacaoNotificacaoService)
    {
        $this->authorize('avaliacoes_listar');

        if (!$this->temPrivilegioGestaoRh()) {
            return response()->json(['msg' => 'Você não tem permissão para notificar pendências.'], 403);
        }

        $enviado = $avaliacaoNotificacaoService->notificarPendenteManual($avaliacaoFeedback, auth()->user());

        if (!$enviado) {
            return response()->json(['msg' => 'Nenhuma notificação foi enviada para esta etapa.'], 422);
        }

        return response()->json(['msg' => 'Notificação enviada com sucesso.']);
    }

    public function notificarPendentes(Request $request, AvaliacaoNotificacaoService $avaliacaoNotificacaoService)
    {
        $this->authorize('avaliacoes_listar');

        if (!$this->temPrivilegioGestaoRh()) {
            return response()->json(['msg' => 'Você não tem permissão para notificar pendências.'], 403);
        }

        $feedbacks = $this->decorarFeedbacks($this->filtroAvaliar($request)->get());

        if ($request->filled('campoLegenda')) {
            $feedbacks = $this->filtrarFeedbacksPorLegenda($feedbacks, $request->campoLegenda)->values();
        }

        $total = $avaliacaoNotificacaoService->notificarPendentesManualmente($feedbacks, auth()->user());

        return response()->json(['msg' => "{$total} notificação(ões) enviada(s) com sucesso."]);
    }

    public function avaliarEdit(AvaliacaoFeedback $avaliacaoFeedback)
    {
        $this->authorize('avaliacoes_avaliar');

        if (!$this->avaliacaoPermiteResponder($avaliacaoFeedback)) {
            return response()->json([
                'msg' => 'Esta avaliação está encerrada ou fora do prazo para resposta.'
            ], 422);
        }

        $avaliacaoTopicos = AvaliacaoTopico::TopicosPais()->with('Subtopicos')->where('avaliacao_tipo_id', $avaliacaoFeedback->avaliacao->avaliacao_tipo_id)->get();
        $respostas = [];
        $respostasFunc = [];

        $mostrarNotasAvaliadorFinal = (bool) optional($avaliacaoFeedback->avaliacao)->mostrar_notas_avaliador_final;
        $avaliacaoFeedbackFunc = null;

        if ($avaliacaoFeedback->principal && $mostrarNotasAvaliadorFinal) {
            $avaliacaoFeedbackFunc = AvaliacaoFeedback::whereAvaliacaoId($avaliacaoFeedback->avaliacao_id)
                ->whereFuncionarioId($avaliacaoFeedback->funcionario_id)
                ->whereAvaliadorId($avaliacaoFeedback->funcionario_id)
                ->first();
        }

        foreach ($avaliacaoTopicos as $topico) {
            foreach ($topico->subtopicos as $subtopico) {
                $avaliacaoResposta = AvaliacaoResposta::where('avaliacao_feedback_id', $avaliacaoFeedback->id)
                    ->where('topico_id', $subtopico->id)->first();

                $respostas[$topico->id][] = [
                    'avaliacao_feedback_id' => $avaliacaoFeedback->id,
                    'topico_id' => $subtopico->id,
                    'nota' => $avaliacaoResposta ? $avaliacaoResposta->nota : ''
                ];

                if ($avaliacaoFeedback->principal && $mostrarNotasAvaliadorFinal && $avaliacaoFeedbackFunc) {
                    $avaliacaoRespostaFunc = AvaliacaoResposta::where('avaliacao_feedback_id', $avaliacaoFeedbackFunc->id)
                        ->where('topico_id', $subtopico->id)->first();

                    $respostasFunc[$topico->id][] = [
                        'avaliacao_feedback_id' => $avaliacaoFeedbackFunc->id,
                        'topico_id' => $subtopico->id,
                        'nota' => $avaliacaoRespostaFunc ? $avaliacaoRespostaFunc->nota : ''
                    ];
                }
            }
        }

        $feedbackCurriculo = FeedbackCurriculo::select(['id', 'curriculo_id', 'empresa_id'])->whereCurriculoId($avaliacaoFeedback->funcionario_id)
            ->whereEmpresaId(auth()->user()->empresa_id)
            ->first();


        $dadosDoFuncionario = [
            'nome' => $avaliacaoFeedback->Funcionario->nome,
            'matricula' => 'NÃO INFORMADO',
            'data_admissao' => 'NÃO INFORMADO',
            'cargo' => 'NÃO INFORMADO',
            'area' => 'NÃO INFORMADO',
            'centro_custo' => 'NÃO INFORMADO',
            'pertence_filial' => false,
        ];

        if ($feedbackCurriculo) {
            $dadosDoFuncionario = Sistema::getColaboradorDados($feedbackCurriculo->curriculo_id, $feedbackCurriculo->empresa_id);
        }

        $outrasAvaliacoesNotas = [];
        if ($avaliacaoFeedback->principal && $mostrarNotasAvaliadorFinal) {
            $outrosFeedbacks = AvaliacaoFeedback::query()
                ->where('avaliacao_id', $avaliacaoFeedback->avaliacao_id)
                ->where('funcionario_id', $avaliacaoFeedback->funcionario_id)
                ->where('id', '!=', $avaliacaoFeedback->id)
                ->where('origem_feedback', AvaliacaoFeedback::ORIGEM_AVALIADOR)
                ->whereIn('status', [AvaliacaoFeedback::STATUS_CONCLUIDA, AvaliacaoFeedback::STATUS_FINAL])
                ->with(['Avaliador:id,nome', 'TipoAvaliador', 'Respostas'])
                ->orderBy('id')
                ->get();

            foreach ($outrosFeedbacks as $of) {
                $respostasOutro = [];
                foreach ($avaliacaoTopicos as $topico) {
                    foreach ($topico->subtopicos as $subtopico) {
                        $avaliacaoResposta = $of->respostas->where('topico_id', $subtopico->id)->first();
                        $respostasOutro[$topico->id][] = [
                            'topico_id' => $subtopico->id,
                            'nota' => $avaliacaoResposta ? $avaliacaoResposta->nota : '',
                        ];
                    }
                }
                $outrasAvaliacoesNotas[] = [
                    'feedback_id' => $of->id,
                    'avaliador_nome' => $of->Avaliador->nome ?? 'Não informado',
                    'tipo_avaliador_label' => optional($of->TipoAvaliador)->label ?? '',
                    'comentario' => $of->comentario ?? '',
                    'respostas' => $respostasOutro,
                ];
            }
        }

        return response()->json([
            'topicos' => $avaliacaoTopicos,
            'avaliacao_feedback_id' => $avaliacaoFeedback->id,
            'respostas' => $respostas,
            'respostas_funcionario' => ($avaliacaoFeedback->principal && $mostrarNotasAvaliadorFinal) ? $respostasFunc : [],
            'comentario' => $avaliacaoFeedback->comentario ?: '',
            'comentario_funcionario' => $avaliacaoFeedbackFunc ? ($avaliacaoFeedbackFunc->comentario ?: '') : '',
            'dados_do_funcionario' => $dadosDoFuncionario,
            'origem_feedback' => $avaliacaoFeedback->origem_feedback,
            'principal' => $avaliacaoFeedback->principal,
            'tipo_pj' => $avaliacaoFeedback->tipo_pj,
            'outras_avaliacoes_notas' => $outrasAvaliacoesNotas,
        ]);
    }

    public function avaliarUpdate(Request $request, AvaliacaoFeedback $avaliacaoFeedback, AvaliacaoNotificacaoService $avaliacaoNotificacaoService)
    {
        $this->authorize('avaliacoes_avaliar');

        if (!$this->avaliacaoPermiteResponder($avaliacaoFeedback)) {
            return response()->json([
                'msg' => 'Esta avaliação está encerrada ou fora do prazo para resposta.'
            ], 422);
        }

        $dados = $request->input();

        $respostas = collect($dados['respostas'])->collapse()->all();

        $dadosValidados = \Validator::make($respostas, [
            '*.nota' => 'required|numeric|min:1|max:5',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Verifique as respostas, existe pergunta sem nota',
            ], 400);

        }

        try {
            DB::beginTransaction();

            foreach ($respostas as $key => $resposta) {
                $avaliacaoFeedback->Respostas()->create($resposta);
            }

            $salvarAvaliacao = $avaliacaoFeedback->update([
                'status' => AvaliacaoFeedback::STATUS_CONCLUIDA,
                'comentario' => $dados['comentario'],
                'fim_feedback' => (new DataHora())->dataHoraInsert()
            ]);

            if ($salvarAvaliacao) {
                $avaliacaoFeedback->refresh();
                $avaliacaoNotificacaoService->notificarProximaEtapaPorConclusao($avaliacaoFeedback);
            }
            DB::commit();
            return response()->json(['msg' => 'Avaliação concluída com sucesso']);
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = "error UPDATE AVALIAR:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            Sistema::LogFormatado($dados);
            return response()->json(['error' => 'Ocorreu um erro'], 500);
        }
    }

    public function avaliarFinal($avaliacaoFeedback)
    {
        $this->authorize('avaliacoes_final');

        $avaliacaoFeedback = AvaliacaoFeedback::find($avaliacaoFeedback);

        if (!$avaliacaoFeedback->principal || $avaliacaoFeedback->avaliador_id != auth()->id() && !$this->temPrivilegioGestaoRh()) {
            return response()->json([
                'msg' => 'Você não tem permissão para acessar essa avaliação',
                'error' => true,
                'status' => 401
            ], 401);
        }

        $avaliacoesFeedbacks = AvaliacaoFeedback::whereAvaliacaoId($avaliacaoFeedback->avaliacao_id)
            ->whereFuncionarioId($avaliacaoFeedback->funcionario_id)
            ->withSum('Respostas', 'nota')
            ->with('Respostas')
            ->orderBy('principal', 'desc')
            ->get();

        $avaliacaoFeedbackFuncionario = AvaliacaoFeedback::whereAvaliacaoId($avaliacaoFeedback->avaliacao_id)
            ->whereOrigemFeedback(AvaliacaoFeedback::ORIGEM_FUNCIONARIO)
            ->whereFuncionarioId($avaliacaoFeedback->funcionario_id)
            ->whereAvaliadorId($avaliacaoFeedback->funcionario_id)
            ->first();

        $resultTopico = [];
        foreach ($avaliacoesFeedbacks as $avalFeedback) {
            foreach ($avalFeedback->respostas as $resposta) {
                $resultTopico[$resposta->topico_id][] = $resposta->nota;
            }
        }

        $resultTopico = collect($resultTopico)->map(function ($item, $key) use ($avaliacoesFeedbacks) {
            $avalTopico = AvaliacaoTopico::find($key);
            return [
                'topico_pai' => AvaliacaoTopico::find($avalTopico->topico_pai_id)->topico,
                'topico_pai_id' => (int)$avalTopico->topico_pai_id,
                'subtopico' => $avalTopico->topico,
                'topico_id' => $avalTopico->id,
                'nota_total' => array_sum($item),
                'media' => array_sum($item) / count($item),
                'avaliadores' => $avaliacoesFeedbacks->map(function ($item) use ($key) {
                    $nome_exp = explode(' ', $item->Avaliador->nome);
                    $nome_avaliador = $nome_exp[0] . ' ' . $nome_exp[count($nome_exp) - 1];

                    $tipoAvaliador = 'Avaliador';
                    if ($item->origem_feedback === AvaliacaoFeedback::ORIGEM_FUNCIONARIO) {
                        $tipoAvaliador = 'Autoavaliação';
                    } elseif ($item->TipoAvaliador && $item->TipoAvaliador->label) {
                        $tipoAvaliador = $item->TipoAvaliador->label;
                        if ($item->principal && !str_contains($tipoAvaliador, '(Avaliador Final)')) {
                            $tipoAvaliador .= ' (Avaliador Final)';
                        }
                    }

                    return [
                        'id' => $item->avaliador_id,
                        'origem' => $item->origem_feedback,
                        'comentario' => $item->comentario,
                        'nome' => mb_strtoupper($nome_avaliador),
                        'tipo' => $tipoAvaliador,
                        'nota' => $item->respostas->where('topico_id', $key)->first()->nota
                    ];
                }),
                'media_redonda' => round(array_sum($item) / count($item)),
            ];
        });

        $totalAval = array_sum($avaliacoesFeedbacks->pluck('respostas_sum_nota')->toArray());

        $topico_pai = $resultTopico->groupBy('topico_pai_id')->reduce(function ($carregar, $item) {
            $carregar[$item[0]['topico_pai']] = [
                'nota_total' => array_sum($item->pluck('nota_total')->toArray()),
                'media' => (float)number_format(array_sum($item->pluck('media')->toArray()) / count($item), 2),
                'media_redonda' => round(array_sum($item->pluck('media')->toArray()) / count($item)),
            ];
            return $carregar;
        }, []);

        $subtopico = $resultTopico->groupBy('subtopico')->reduce(function ($carregar, $item) {
            $carregar[$item[0]['subtopico']] = [
                'nota_total' => array_sum($item->pluck('nota_total')->toArray()),
                'media' => (float)number_format(array_sum($item->pluck('media')->toArray()) / count($item), 2),
                'media_redonda' => round(array_sum($item->pluck('media')->toArray()) / count($item)),
            ];
            return $carregar;
        }, []);

        $result_topico_agrupado = $resultTopico->groupBy('topico_pai_id')->reduce(function ($carregar, $item) {
            $carregar[] = $item;
            return $carregar;
        }, []);

        $resultChart = $resultTopico->groupBy('topico_pai_id')->reduce(function ($carregar, $item) {
            $carregar[] = [
                'name' => $item[0]['topico_pai'],
                'data' => [
                    'labels' => range(1, count($item)),
                    'datasets' => [
                        [
                            'label' => "",
                            'backgroundColor' => 'rgba(255,255,255,0.2)',
                            'borderColor' => "orange",
                            'pointBackgroundColor' => "orange",
                            'pointBorderColor' => "#fff",
                            'pointHoverBackgroundColor' => "#fff",
                            'pointHoverBorderColor' => "rgba(239,78,261)",
                            'data' => $item->pluck('media_redonda')->toArray(),
                        ]
                    ]
                ],
            ];
            return $carregar;
        }, []);

        $feedbackCurriculo = FeedbackCurriculo::whereCurriculoId($avaliacaoFeedback->funcionario_id)
            ->whereEmpresaId(auth()->user()->empresa_id)
            ->first();

        $dadosDoFuncionario = [
            'nome' => $avaliacaoFeedback->Funcionario->nome,
            'matricula' => 'NÃO INFORMADO',
            'data_admissao' => 'NÃO INFORMADO',
            'cargo' => 'NÃO INFORMADO',
            'area' => 'NÃO INFORMADO',
            'centro_custo' => 'NÃO INFORMADO',
            'pertence_filial' => false,
        ];

        // Variável para dados da empresa com valor padrão
        $dadosEmpresa = null;

        if ($feedbackCurriculo) {
            $dadosDoFuncionario = Sistema::getColaboradorDados($feedbackCurriculo->curriculo_id, $feedbackCurriculo->empresa_id);

            // Verificar se existe a relação Admissao antes de acessar
            if ($feedbackCurriculo->Admissao) {
                $dadosEmpresa = Sistema::getEmpresaFilialMatriz($feedbackCurriculo->Admissao->centro_custo_filial_id, $feedbackCurriculo->empresa_id);
            }
        }

        if (!$dadosEmpresa) {
            $dadosEmpresa = Sistema::getEmpresa(auth()->user()->empresa_id);
        }

        $total_questoes = collect($result_topico_agrupado)->collapse()->count();
        $nota_final = (float)number_format((($totalAval / count($avaliacoesFeedbacks)) / $total_questoes) / count($result_topico_agrupado), 2, '.', '.');

        return [
            'dados_do_funcionario' => $dadosDoFuncionario,
            'dados_empresa' => $dadosEmpresa, // Agora pode ser null se não houver dados
            'solicitante' => User::select('nome')->find(auth()->id())->nome,
            'avaliador_principal' => $avaliacaoFeedback->wherePrincipal(true)->first()->Avaliador->nome,
            'status_avaliacao' => $avaliacaoFeedback->status,
            'total_aval' => $totalAval,
            'total_aval_feed' => count($avaliacoesFeedbacks),
            'nota_final' => $nota_final,
            'resultado_topico_pai' => $topico_pai,
            'result_topico_pai_agrupado' => $result_topico_agrupado,
            'result_topico' => $resultTopico,
            'result_subtopico' => $subtopico,
            'resultChart' => $resultChart,
            'avaliacao_feedback_id' => $avaliacaoFeedbackFuncionario->id,
            'avaliacao_feedback_id_avaliador' => $avaliacaoFeedback->id,
            'gestor_id' => $avaliacaoFeedback->wherePrincipal(true)->first()->avaliador_id,
            'planos_acoes' => AvaliacaoResultado::with('Topico')->where('avaliacao_feedback_id', $avaliacaoFeedbackFuncionario->id)->where('gestor_id', $avaliacaoFeedback->wherePrincipal(true)->first()->avaliador_id)->get(),
            'planos_acoes_delete' => [],
            'token' => \Crypt::encrypt($avaliacaoFeedback->id),
            'titulo_avaliacao' => $avaliacaoFeedback->Avaliacao->titulo,
            'tipo_avaliacao' => $avaliacaoFeedback->Avaliacao->AvaliacaoTipo->nome,
            'tipo_pj' => $avaliacaoFeedback->tipo_pj,
            'fluxo_etapas' => Avaliacao::fluxoAvaliacao($avaliacaoFeedback->avaliacao_id)->values()->all(),
        ];
    }

    public function imprimir($token)
    {
        $token = \Crypt::decrypt($token);
        $dados = $this->avaliarFinal($token);
        $tipo_pj = $dados['tipo_pj'];

        if (! empty($dados['planos_acoes'])) {
            foreach ($dados['planos_acoes'] as $plano) {
                if ($plano instanceof \Illuminate\Database\Eloquent\Model) {
                    $plano->setAttribute(
                        'plano_de_acao',
                        \App\Support\AvaliacaoPlanoAcaoImpressaoHtmlSanitizer::sanitize(
                            (string) $plano->getAttribute('plano_de_acao')
                        )
                    );
                }
            }
        }

        return view('pdf.avaliacoes.desempenho', compact('dados', 'tipo_pj'));
    }

    public function salvaAvaliacao(Request $request, AvaliacaoFeedback $avaliacaoFeedback)
    {
        $this->authorize('avaliacoes_final');
        $dados = $request->input();

        if (!isset($dados['planos_acoes'])) {
            return response()->json([
                'msg' => 'ERRO: É necessário inserir um plano de ação',
            ], 400);
        }

        $dadosValidados = \Validator::make($dados['planos_acoes'], [
            'topico_id.*' => 'required',
            'plano_de_acao.*' => 'required',
            'responsavel.*' => 'required',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao salvar a avaliação final',
                'erros' => $dadosValidados->errors()
            ], 400);

        }

        try {
            DB::beginTransaction();

            if (isset($dados['planos_acoes_delete'])) {
                foreach ($dados['planos_acoes_delete'] as $lin) {
                    AvaliacaoResultado::find($lin)->delete();
                }
            }

            foreach ($dados['planos_acoes'] as $item) {
                if (isset($item['nova'])) {
                    //criar
                    $aval = AvaliacaoResultado::create([
                        'avaliacao_feedback_id' => $dados['avaliacao_feedback_id'],
                        'topico_id' => $item['topico_id'],
                        'plano_de_acao' => $item['plano_de_acao'],
                        'inicio' => $item['inicio'],
                        'termino' => $item['termino'],
                        'responsavel' => $item['responsavel'],
                        'status' => AvaliacaoResultado::STATUS_DEFINIDO,
                        'gestor_id' => $dados['gestor_id'],
                    ]);


                } else {
                    //atualizar
                    $aval = AvaliacaoResultado::find($item['id'])->update([
                        'topico_id' => $item['topico_id'],
                        'plano_de_acao' => $item['plano_de_acao'],
                        'responsavel' => $item['responsavel'],
                        'inicio' => $item['inicio'],
                        'termino' => $item['termino'],
                        'status' => AvaliacaoResultado::STATUS_DEFINIDO,
                    ]);
                }
            }

            AvaliacaoFeedback::whereAvaliacaoId($avaliacaoFeedback->avaliacao_id)
                ->whereFuncionarioId($avaliacaoFeedback->funcionario_id)
                ->whereStatus(AvaliacaoFeedback::STATUS_CONCLUIDA)
                ->update([
                    'status' => AvaliacaoFeedback::STATUS_FINAL,
                    'fim_feedback' => (new DataHora())->dataHoraInsert()
                ]);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            $msg = "error SALVAR AVALIAÇÃO:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            Sistema::LogFormatado($dados);
            return response()->json(['error' => 'Ocorreu um erro'], 500);
        }
    }
}
