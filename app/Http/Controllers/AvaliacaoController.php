<?php

namespace App\Http\Controllers;

use App\Jobs\JobAutoAvaliacaoConcluida;
use App\Models\Avaliacao;
use App\Models\AvaliacaoFeedback;
use App\Models\AvaliacaoResposta;
use App\Models\AvaliacaoResultado;
use App\Models\AvaliacaoTipo;
use App\Models\AvaliacaoTopico;
use App\Models\FeedbackCurriculo;
use App\Models\Sistema;
use App\Models\User;
use App\Rules\TenantUniqueRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class AvaliacaoController extends Controller
{

    protected function temPrivilegioGestaoRh(): bool
    {
        return (bool)in_array('privilegio_gestao_rh', auth()->user()->listaDeHabilidades());
    }

    public function index(Request $request)
    {
        return view('g.cadastros.avaliacoes.avaliacao.index');
    }

    public function store(Request $request)
    {
        $this->authorize('cadastro_avaliacao_insert');
        $dados = $request->input();
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

        return $avaliacao;
    }

    public function show(Avaliacao $avaliacao)
    {

    }

    public function update(Request $request, Avaliacao $avaliacao)
    {
        $this->authorize('cadastro_avaliacao_update');
        $dados = $request->input();
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
     * @return \Illuminate\Database\Query\Builder
     */
    private function filtro(Request $request)
    {
        $resultado = Avaliacao::with('AvaliacaoTipo')
            ->orderBy('data_inicio_prazo');
        if ($request->filled('campoBusca')) {
            $resultado->where("titulo", "like", "%$request->campoBusca%")
                ->orWhere('id', $request->campoBusca);
        }
        return $resultado;
    }

    public function atualizar(Request $request)
    {
        $this->authorize('cadastro_avaliacao');
        $resultado = $this->filtro($request)->paginate($request->porPag ?: 20);
        $avaliacoes_tipos = AvaliacaoTipo::whereAtivo(true)->get();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'avaliacoes_tipos' => $avaliacoes_tipos,
                'lista_status' => Avaliacao::LISTA_STATUS,
            ]
        ]);
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
        $resultado = $this->filtroAvaliar($request)->paginate($request->porPag ?: 20);
        $avaliacoes_tipos = AvaliacaoTipo::whereAtivo(true)->get();
        $lista_avaliacoes = Avaliacao::whereAtivo(true)->orderBy('titulo')->get();
        $avaliacoesFeedbacks = collect($resultado->items())->transform(function ($item) {

            $avaliacaoFeedbackFunc = AvaliacaoFeedback::whereAvaliacaoId($item->avaliacao_id)->whereFuncionarioId($item->funcionario_id);
            $avaliacaoPar = clone $avaliacaoFeedbackFunc;
            $avaliacaoPar = $avaliacaoPar->where('origem_feedback', AvaliacaoFeedback::ORIGEM_AVALIADOR)->where('principal', false);
            $totalAvaliacaoPar = $avaliacaoPar->count();
            $totalAvaliacaoParConcluida = $avaliacaoPar->where('status', AvaliacaoFeedback::STATUS_CONCLUIDA)->count();

            $item->total_avaliacoes = $avaliacaoFeedbackFunc->count();
            $totalAvaliacoesFuncConcluidas = $avaliacaoFeedbackFunc->whereStatus(AvaliacaoFeedback::STATUS_CONCLUIDA);
            $item->total_avaliacoes_concluidas = $totalAvaliacoesFuncConcluidas->count();
            $item->fez_auto_avaliacao = $totalAvaliacoesFuncConcluidas->count() > 0;
            $item->fazer_avaliacao_final = $item->principal && $item->total_avaliacoes_concluidas === $item->total_avaliacoes;

            $item->pendente_autoavaliacao = $item->avaliador_id == $item->funcionario_id && !$item->fez_auto_avaliacao;
            $item->pendente_autoavaliacao_colaborador = $item->avaliador_id != $item->funcionario_id && $item->status == 'Pendente' && !$item->fez_auto_avaliacao;
            $item->pendente_avaliacao_par = $totalAvaliacaoPar != $totalAvaliacaoParConcluida;
            $item->pendente_avaliacao_gestor = $item->total_avaliacoes - $item->total_avaliacoes_concluidas;
            $item->token = \Crypt::encrypt($item->id);
            $item->titulo_avaliacao = $item->Avaliacao->titulo;
            $item->tipo_avaliacao = $item->Avaliacao->AvaliacaoTipo->nome;

            if (!$item->Avaliacao->auto_avaliacao) {
                $item->total_avaliacoes = $avaliacaoFeedbackFunc->count();
                $item->pendente_avaliacao_gestor = $item->total_avaliacoes - $item->total_avaliacoes_concluidas;
                $item->fazer_avaliacao_final = $item->principal && $item->total_avaliacoes_concluidas === $item->total_avaliacoes && $item->status != 'Finalizada';
            }

            return $item;
        });

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $avaliacoesFeedbacks,
                'avaliacoes_tipos' => $avaliacoes_tipos,
                'lista_avaliacoes' => $lista_avaliacoes,
                'lista_status' => Avaliacao::LISTA_STATUS,
                'tem_privilegio_gestao_rh' => $this->temPrivilegioGestaoRh(),
            ]
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Database\Query\Builder
     */
    private function filtroAvaliar(Request $request)
    {

//        $resultado = AvaliacaoFeedback::with('Avaliacao.AvaliacaoTipo', 'Funcionario:id,nome,login,temp,ativo,deleted_at', 'Avaliador:id,nome,login')
//            ->whereHas('Funcionario', function ($query) {
//                $query->ativoNaoExcluido();
//            })->whereHas('Avaliador', function ($query) {
//                $query->ativoNaoExcluido();
//            })->whereAvaliadorId(auth()->user()->id);

        $resultado = AvaliacaoFeedback::with('Avaliacao.AvaliacaoTipo', 'Funcionario:id,nome,login,temp,ativo,deleted_at', 'Avaliador:id,nome,login')
            ->whereHas('Funcionario', function ($query) {
                $query->ativoNaoExcluido();
            })->whereHas('Avaliador', function ($query) {
                $query->ativoNaoExcluido();
            });
        if (!$this->temPrivilegioGestaoRh()) {
            $resultado->whereAvaliadorId(auth()->user()->id);
        }

        $resultado->whereHas('Avaliacao', function ($query) {
            $query->whereIn('status', [Avaliacao::STATUS_ABERTA, Avaliacao::STATUS_ENCERRADA])
                ->whereAtivo(true);
        });

        if ($request->filled('campoBusca')) {
            $resultado->where(function ($query) use ($request) {
                $query->where('titulo', 'like', "%$request->campoBusca%")
                    ->orWhere('id', $request->campoBusca);
            });
        }

        $Avaliacao = Avaliacao::select(['id', 'auto_avaliacao'])->whereAtivo(true)->orderBy('titulo')->limit(1)->first();

        if ($request->filled('campoAvaliacao')) {
            $Avaliacao = Avaliacao::select(['id', 'auto_avaliacao'])->whereId($request->campoAvaliacao)->first();

            if ($request->filled('campoStatus')) {
                $resultado->whereStatus($request->campoStatus);
            }

            if (!$Avaliacao->auto_avaliacao) {
                $resultado->where('principal', true);
            }
            $resultado->where('avaliacao_id', $request->campoAvaliacao);
        } else {
            if (!$Avaliacao->auto_avaliacao) {
                $resultado->where('principal', true);
            }
            $resultado->where('avaliacao_id', $Avaliacao->id);
        }

        return $resultado;
    }

    public function avaliarIndex(Request $request)
    {
        return view('g.cadastros.avaliacoes.avaliar.index');
    }

    public function avaliarEdit(AvaliacaoFeedback $avaliacaoFeedback)
    {
        $this->authorize('avaliacoes_avaliar');
        $avaliacaoTopicos = AvaliacaoTopico::TopicosPais()->with('Subtopicos')->where('avaliacao_tipo_id', $avaliacaoFeedback->avaliacao->avaliacao_tipo_id)->get();
        $respostas = [];
        $respostasFunc = [];

        $avaliacaoFeedbackFunc = AvaliacaoFeedback::whereAvaliacaoId($avaliacaoFeedback->avaliacao_id)
            ->whereFuncionarioId($avaliacaoFeedback->funcionario_id)
            ->whereAvaliadorId($avaliacaoFeedback->funcionario_id)
            ->first();

        foreach ($avaliacaoTopicos as $topico) {
            foreach ($topico->subtopicos as $subtopico) {
                $avaliacaoResposta = AvaliacaoResposta::where('avaliacao_feedback_id', $avaliacaoFeedback->id)
                    ->where('topico_id', $subtopico->id)->first();

                $respostas[$topico->id][] = [
                    'avaliacao_feedback_id' => $avaliacaoFeedback->id,
                    'topico_id' => $subtopico->id,
                    'nota' => $avaliacaoResposta ? $avaliacaoResposta->nota : ''
                ];

                $avaliacaoRespostaFunc = AvaliacaoResposta::where('avaliacao_feedback_id', $avaliacaoFeedbackFunc->id)
                    ->where('topico_id', $subtopico->id)->first();

                $respostasFunc[$topico->id][] = [
                    'avaliacao_feedback_id' => $avaliacaoFeedbackFunc->id,
                    'topico_id' => $subtopico->id,
                    'nota' => $avaliacaoRespostaFunc ? $avaliacaoRespostaFunc->nota : ''
                ];
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

        return response()->json([
            'topicos' => $avaliacaoTopicos,
            'avaliacao_feedback_id' => $avaliacaoFeedback->id,
            'respostas' => $respostas,
            'respostas_funcionario' => $avaliacaoFeedback->principal ? $respostasFunc : [],
            'comentario' => $avaliacaoFeedback->comentario ?: '',
            'comentario_funcionario' => $avaliacaoFeedbackFunc->comentario ?: '',
            'dados_do_funcionario' => $dadosDoFuncionario,
            'origem_feedback' => $avaliacaoFeedback->origem_feedback,
            'principal' => $avaliacaoFeedback->principal,
        ]);
    }

    public function avaliarUpdate(Request $request, AvaliacaoFeedback $avaliacaoFeedback)
    {
        $this->authorize('avaliacoes_avaliar');

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

            $dados_job = [];

            if ($salvarAvaliacao && $avaliacaoFeedback->origem_feedback === AvaliacaoFeedback::ORIGEM_FUNCIONARIO && $avaliacaoFeedback->status === AvaliacaoFeedback::STATUS_CONCLUIDA) {
                $avaliadores = AvaliacaoFeedback::select(['id', 'empresa_id', 'avaliacao_id', 'avaliador_id', 'funcionario_id', 'status'])
                    ->where('avaliacao_id', $avaliacaoFeedback->avaliacao_id)
                    ->where('funcionario_id', $avaliacaoFeedback->funcionario_id)
                    ->with('Avaliador:id,nome,login')
                    ->with('Funcionario:id,nome')
                    ->with('Avaliacao:id,titulo')
                    ->OrigemAvaliador()
                    ->get();

                foreach ($avaliadores as $avaliador) {

                    $dados_job['nome'] = $avaliador->avaliador->nome;
                    $dados_job['email'] = $avaliador->avaliador->login;
                    $dados_job['funcionario'] = $avaliador->funcionario->nome;
                    $dados_job['avaliacao'] = $avaliador->avaliacao->titulo;
                    $dados_job['empresa_id'] = $avaliacaoFeedback->empresa_id;

                    JobAutoAvaliacaoConcluida::dispatch([
                        'nome' => $dados_job['nome'],
                        'email' => $dados_job['email'],
                        'funcionario' => $dados_job['funcionario'],
                        'avaliacao' => $dados_job['avaliacao'],
                        'empresa_id' => $dados_job['empresa_id']
                    ]);

                    $dados_job = [];
                }
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

//        $avaliacoesFeedbacks = AvaliacaoFeedback::whereAvaliacaoId($avaliacaoFeedback->avaliacao_id)
//            ->whereOrigemFeedback(AvaliacaoFeedback::ORIGEM_AVALIADOR)
//            ->whereFuncionarioId($avaliacaoFeedback->funcionario_id)
//            ->withSum('Respostas', 'nota')
//            ->with('Respostas')
//            ->orderBy('principal', 'desc')
//            ->get();

        $avaliacoesFeedbacks = AvaliacaoFeedback::whereAvaliacaoId($avaliacaoFeedback->avaliacao_id)
//            ->whereOrigemFeedback(AvaliacaoFeedback::ORIGEM_AVALIADOR)
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
                    return [
                        'id' => $item->avaliador_id,
                        'origem' => $item->origem_feedback,
                        'comentario' => $item->comentario,
                        'nome' => mb_strtoupper($nome_avaliador),
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


        if ($feedbackCurriculo) {
            $dadosDoFuncionario = Sistema::getColaboradorDados($feedbackCurriculo->curriculo_id, $feedbackCurriculo->empresa_id);

        }

        $total_questoes = collect($result_topico_agrupado)->collapse()->count();
        $nota_final = (float)number_format((($totalAval / count($avaliacoesFeedbacks)) / $total_questoes) / count($result_topico_agrupado), 2, '.', '.');
        // total de avaliações / total de avaliadores / total de questoes / total de topicos


        return [
            'dados_do_funcionario' => $dadosDoFuncionario,
            'dados_empresa' => Sistema::getEmpresaFilialMatriz($feedbackCurriculo->Admissao->centro_custo_filial_id, $feedbackCurriculo->empresa_id),
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
        ];
    }

    public function imprimir($token)
    {
        $token = \Crypt::decrypt($token);
        $dados = $this->avaliarFinal($token);

        return view('pdf.avaliacoes.desempenho', compact('dados'));
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
