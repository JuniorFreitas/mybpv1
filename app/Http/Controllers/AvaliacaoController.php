<?php

namespace App\Http\Controllers;

use App\Models\Avaliacao;
use App\Models\AvaliacaoFeedback;
use App\Models\AvaliacaoResposta;
use App\Models\AvaliacaoTipo;
use App\Models\AvaliacaoTopico;
use App\Models\FeedbackCurriculo;
use App\Models\User;
use App\Rules\TenantUniqueRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use MasterTag\DataHora;

class AvaliacaoController extends Controller
{
    public function index(Request $request)
    {
        return view('g.cadastros.avaliacoes.avaliacao.index');
    }

    public function store(Request $request)
    {
//        $this->authorize('administracao_documentos_legais_insert');
        $dados = $request->input();
//        dd($dados);
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
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function edit(Avaliacao $avaliacao)
    {
        return $avaliacao;
    }

    public function show(AvaliacaoTipo $avaliacaotipo)
    {
//        $this->authorize('administracao_documentos_legais_insert');
        return $avaliacaotipo;
    }

    public function update(Request $request, Avaliacao $avaliacao)
    {
        //        $this->authorize('administracao_documentos_legais_insert');
        $dados = $request->input();
//        dd($dados);
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
        $resultado = $this->filtro($request)->paginate($request->porPag ?: 20);
        $avaliacoes_tipos = AvaliacaoTipo::whereAtivo(true)->get();

        $permissoes = [
//            'insert' => auth()->user()->can('administracao_documentos_legais_tipos_documentos_insert'),
//            'update' => auth()->user()->can('administracao_documentos_legais_tipos_documentos_update'),
//            'delete' => auth()->user()->can('administracao_documentos_legais_tipos_documentos_delete')
        ];

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'avaliacoes_tipos' => $avaliacoes_tipos,
                'lista_status' => Avaliacao::LISTA_STATUS,
//                'permissoes' => [
//                    'admissao_cih_lancar' => auth()->user()->can('admissao_cih_lancar'),
//                    'admissao_cih_aprovar' => auth()->user()->can('admissao_cih_aprovar'),
//                    'admissao_cih_privilegio_adm' => auth()->user()->can('admissao_cih_privilegio_adm'),
//                ]
            ]
        ]);
    }

    public function ativaDesativa(Request $request)
    {
//        $this->authorize('administracao_documentos_legais_insert');

        $avaliacao = Avaliacao::find($request->id);
        $avaliacao->ativo = !$avaliacao->ativo;
        $avaliacao->save();
        $avaliacao->refresh();
        return response()->json(['ativo' => $avaliacao->ativo], 201);
    }


    public function atualizarAvaliar(Request $request)
    {
        $resultado = $this->filtroAvaliar($request)->paginate($request->porPag ?: 20);
        $avaliacoes_tipos = AvaliacaoTipo::whereAtivo(true)->get();

        $avaliacoesFeedbacks = collect($resultado->items())->transform(function ($item) {
            $avaliacaoFeedbackFunc = AvaliacaoFeedback::whereAvaliacaoId($item->avaliacao_id)->whereFuncionarioId($item->funcionario_id);
            $item->total_avaliacoes = $avaliacaoFeedbackFunc->count();
            $totalAvaliacoesFuncConcluidas = $avaliacaoFeedbackFunc->whereStatus(AvaliacaoFeedback::STATUS_CONCLUIDA);
            $item->total_avaliacoes_concluidas = $totalAvaliacoesFuncConcluidas->count();
            $item->fazer_avaliacao_final = $item->principal && $item->total_avaliacoes_concluidas === $item->total_avaliacoes;
            return $item;
        });

        $permissoes = [
//            'insert' => auth()->user()->can('administracao_documentos_legais_tipos_documentos_insert'),
//            'update' => auth()->user()->can('administracao_documentos_legais_tipos_documentos_update'),
//            'delete' => auth()->user()->can('administracao_documentos_legais_tipos_documentos_delete')
        ];

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'avaliacoes_tipos' => $avaliacoes_tipos,
                'lista_status' => Avaliacao::LISTA_STATUS,
//                'permissoes' => [
//                    'admissao_cih_lancar' => auth()->user()->can('admissao_cih_lancar'),
//                    'admissao_cih_aprovar' => auth()->user()->can('admissao_cih_aprovar'),
//                    'admissao_cih_privilegio_adm' => auth()->user()->can('admissao_cih_privilegio_adm'),
//                ]
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
        $resultado = AvaliacaoFeedback::with('Avaliacao.AvaliacaoTipo', 'Funcionario', 'Avaliador')
            ->whereAvaliadorId(auth()->user()->id);

        $resultado->whereHas('Avaliacao', function ($query) {
            $query->whereStatus(Avaliacao::STATUS_ABERTA)
                ->whereAtivo(true);
        });

//            ->whereFeedbackId()
//            ->orderBy('avaliacoes.data_inicio_prazo');
        if ($request->filled('campoBusca')) {
            $resultado->where("titulo", "like", "%$request->campoBusca%")
                ->orWhere('id', $request->campoBusca);
        }
        return $resultado;
    }

    public function avaliarIndex(Request $request)
    {
        return view('g.cadastros.avaliacoes.avaliar.index');
    }

    public function avaliarEdit(AvaliacaoFeedback $avaliacaoFeedback)
    {
        $avaliacaoTopicos = AvaliacaoTopico::TopicosPais()->with('Subtopicos')->where('avaliacao_tipo_id', $avaliacaoFeedback->avaliacao->avaliacao_tipo_id)->get();
        $respostas = [];

        foreach ($avaliacaoTopicos as $topico) {
            foreach ($topico->subtopicos as $subtopico) {
                $avaliacaoResposta = AvaliacaoResposta::where('avaliacao_feedback_id', $avaliacaoFeedback->id)
                    ->where('topico_id', $subtopico->id)->first();
                $respostas[$topico->id][] = [
                    'avaliacao_feedback_id' => $avaliacaoFeedback->id,
                    'topico_id' => $subtopico->id,
                    'nota' => $avaliacaoResposta ? $avaliacaoResposta->nota : ''
                ];
            }
        }

        $feedbackCurriculo = FeedbackCurriculo::whereCurriculoId($avaliacaoFeedback->funcionario_id)
            ->whereEmpresaId(auth()->user()->empresa_id)
            ->first();


        $dadosDoFuncionario = [
            'nome' => $avaliacaoFeedback->Funcionario->nome,
            'matricula' => 'NÃO INFORMADO',
            'data_admissao' => 'NÃO INFORMADO',
            'cargo' => 'NÃO INFORMADO',
            'area' => 'NÃO INFORMADO',
        ];

        if ($feedbackCurriculo) {
            $admissao = $feedbackCurriculo->Admissao;
            $dadosDoFuncionario = [
                'nome' => $avaliacaoFeedback->Funcionario->nome,
                'matricula' => $admissao->matricula ?: "NÃO INFORMADO",
                'data_admissao' => $admissao->data_admissao,
                'cargo' => $admissao->cargo,
                'area' => $admissao->AreaEtiqueta ? $admissao->AreaEtiqueta->label : "NÃO INFORMADO",
            ];
        }

        return response()->json([
            'topicos' => $avaliacaoTopicos,
            'avaliacao_feedback_id' => $avaliacaoFeedback->id,
            'respostas' => $respostas,
            'comentario' => $avaliacaoFeedback->comentario ?: '',
            'dados_do_funcionario' => $dadosDoFuncionario,
//                'avaliacoes_tipos' => $avaliacoes_tipos,
//                'lista_status' => Avaliacao::LISTA_STATUS,
//                'permissoes' => [
//                    'admissao_cih_lancar' => auth()->user()->can('admissao_cih_lancar'),
//                    'admissao_cih_aprovar' => auth()->user()->can('admissao_cih_aprovar'),
//                    'admissao_cih_privilegio_adm' => auth()->user()->can('admissao_cih_privilegio_adm'),
        ]);
    }

    public function avaliarUpdate(Request $request, AvaliacaoFeedback $avaliacaoFeedback)
    {
        //        $this->authorize('administracao_documentos_legais_insert');

        $dados = $request->input();

        $respostas = collect($dados['respostas'])->collapse()->all();
//        $dadosValidados = \Validator::make($dados, [
//            'respostas.*.nota' => 'required|numeric|min:0|max:5',
//        ]);
//
//        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
//            return response()->json([
//                'msg' => 'Verifique as respostas, existe pergunta sem nota',
//            ], 400);
//
//        }


        foreach ($respostas as $key => $resposta) {
            $avaliacaoFeedback->Respostas()->create($resposta);
//            AvaliacaoResposta::create($resposta);
        }

        $avaliacaoFeedback->update([
            'status' => AvaliacaoFeedback::STATUS_CONCLUIDA,
            'comentario' => $dados['comentario'],
            'fim_feedback' => (new DataHora())->dataHoraInsert()
        ]);
    }

    public function avaliarFinal(AvaliacaoFeedback $avaliacaoFeedback)
    {
        if (!$avaliacaoFeedback->principal || $avaliacaoFeedback->avaliador_id != auth()->id()) {
            return response()->json([
                'msg' => 'Você não tem permissão para acessar essa avaliação',
                'error' => true,
                'status' => 401
            ], 401);
        }

        $avaliacoesFeedbacks = AvaliacaoFeedback::whereAvaliacaoId($avaliacaoFeedback->avaliacao_id)
            ->whereOrigemFeedback(AvaliacaoFeedback::ORIGEM_AVALIADOR)
            ->whereFuncionarioId($avaliacaoFeedback->funcionario_id)
            ->withSum('Respostas', 'nota')
            ->with('Respostas')
            ->get();

        $resultTopico = [];
        foreach ($avaliacoesFeedbacks as $avalFeedback) {
            foreach ($avalFeedback->respostas as $resposta) {
                $resultTopico[$resposta->topico_id][] = $resposta->nota;
            }
        }

        $resultTopico = collect($resultTopico)->map(function ($item, $key) {
            $avalTopico = AvaliacaoTopico::find($key);
            return [
                'topico_pai' => AvaliacaoTopico::find($avalTopico->topico_pai_id)->topico,
                'topico_pai_id' => (int)$avalTopico->topico_pai_id,
                'subtopico' => $avalTopico->topico,
                'nota_total' => array_sum($item),
                'media' => array_sum($item) / count($item),
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
            $carregar[$item[0]['topico_pai']] = $item;
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
        ];

        if ($feedbackCurriculo) {
            $admissao = $feedbackCurriculo->Admissao;
            $dadosDoFuncionario = [
                'nome' => $avaliacaoFeedback->Funcionario->nome,
                'matricula' => $admissao->matricula ?: "NÃO INFORMADO",
                'data_admissao' => $admissao->data_admissao,
                'cargo' => $admissao->cargo,
                'area' => $admissao->AreaEtiqueta ? $admissao->AreaEtiqueta->label : "NÃO INFORMADO",
            ];
        }

        return response()->json([
            'dados_do_funcionario' => $dadosDoFuncionario,
            'avaliador_principal' => $avaliacaoFeedback->wherePrincipal(true)->first()->Avaliador->nome,
            'status_avaliacao' => $avaliacaoFeedback->status,
            'total_aval' => $totalAval,
            'media_aval' => $totalAval / count($avaliacoesFeedbacks),
            'resultado_topico_pai' => $topico_pai,
            'result_topico_pai_agrupado' => $result_topico_agrupado,
            'result_topico' => $resultTopico,
            'result_subtopico' => $subtopico,
        ]);


        die();

        $avaliacaoTopicos = AvaliacaoTopico::TopicosPais()->with('Subtopicos')->where('avaliacao_tipo_id', $avaliacaoFeedback->avaliacao->avaliacao_tipo_id)->get();
        $respostas = [];

        $avaliacoesFeedbacks = AvaliacaoFeedback::whereAvaliacaoId($avaliacaoFeedback->avaliacao_id)
            ->whereOrigemFeedback(AvaliacaoFeedback::ORIGEM_AVALIADOR)
            ->whereFuncionarioId($avaliacaoFeedback->funcionario_id)->get();

        $qtdAvalFeedbacks = count($avaliacoesFeedbacks);

        $resultado_final = [];

        foreach ($avaliacaoTopicos as $topico) {
            foreach ($topico->subtopicos as $subtopico) {
                foreach ($avaliacoesFeedbacks as &$avalFeedback) {
                    $avaliacaoResposta = AvaliacaoResposta::where('avaliacao_feedback_id', $avalFeedback->id)
                        ->where('topico_id', $subtopico->id)->first();
                    $respostas[$topico->id][$avalFeedback->id][$subtopico->id] = [
                        'nota' => $avaliacaoResposta ? $avaliacaoResposta->nota : '',
                    ];
                }

//                $resultado_final[$avalFeedback][$subtopico->id]['nota_final'] = $respostas[$avalFeedback->id][$topico->id][$subtopico->id]['nota'];
//                $resultado_final[$avalFeedback->id][$subtopico->id]['nota_final'] = $avalFeedback->id + 1;

            }
        }


        $notaPorTopico = [];


        return $resultado_final;

        $feedbackCurriculo = FeedbackCurriculo::whereCurriculoId($avaliacaoFeedback->funcionario_id)
            ->whereEmpresaId(auth()->user()->empresa_id)
            ->first();


        $dadosDoFuncionario = [
            'nome' => $avaliacaoFeedback->Funcionario->nome,
            'matricula' => 'NÃO INFORMADO',
            'data_admissao' => 'NÃO INFORMADO',
            'cargo' => 'NÃO INFORMADO',
            'area' => 'NÃO INFORMADO',
        ];

        if ($feedbackCurriculo) {
            $admissao = $feedbackCurriculo->Admissao;
            $dadosDoFuncionario = [
                'nome' => $avaliacaoFeedback->Funcionario->nome,
                'matricula' => $admissao->matricula ?: "NÃO INFORMADO",
                'data_admissao' => $admissao->data_admissao,
                'cargo' => $admissao->cargo,
                'area' => $admissao->AreaEtiqueta ? $admissao->AreaEtiqueta->label : "NÃO INFORMADO",
            ];
        }

        return response()->json([
            'topicos' => $avaliacaoTopicos,
            'avaliacao_feedback_id' => $avaliacaoFeedback->id,
            'respostas' => $respostas,
            'comentario' => $avaliacaoFeedback->comentario ?: '',
            'dados_do_funcionario' => $dadosDoFuncionario,
//                'avaliacoes_tipos' => $avaliacoes_tipos,
//                'lista_status' => Avaliacao::LISTA_STATUS,
//                'permissoes' => [
//                    'admissao_cih_lancar' => auth()->user()->can('admissao_cih_lancar'),
//                    'admissao_cih_aprovar' => auth()->user()->can('admissao_cih_aprovar'),
//                    'admissao_cih_privilegio_adm' => auth()->user()->can('admissao_cih_privilegio_adm'),
        ]);
    }

}
