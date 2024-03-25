<?php

namespace App\Http\Controllers;

use App\Models\Avaliacao;
use App\Models\AvaliacaoFeedback;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\HigherOrderWhenProxy;

class AvaliadorController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
    public function index()
    {
        return view('g.cadastros.avaliacoes.avaliador.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\AvaliacaoFeedback $avaliador
     * @return AvaliacaoFeedback
     */
    public function show(AvaliacaoFeedback $avaliador)
    {
        $this->authorize('cadastro_avaliacao_vincular_avaliadores');
        return $avaliador;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\AvaliacaoFeedback $avaliador
     * @return \Illuminate\Http\Response
     */
    public function edit(AvaliacaoFeedback $avaliador)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\AvaliacaoFeedback $avaliador
     * @return JsonResponse
     */
    public function update(Request $request, AvaliacaoFeedback $avaliador)
    {
        $this->authorize('cadastro_avaliacao_vincular_avaliadores');
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'tipo_frequencia' => 'required|min:1',
            'limite_tolerancia' => 'required|numeric|min:1',
            'tempo_limite_falta' => 'required|numeric|min:1',
            'tempo_limite_saida' => 'required|numeric|min:1',
            'dia_nova_frequencia' => 'required|numeric|min:1',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao salvar as configuraçôes da empresa',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            \DB::beginTransaction();
            $avaliador->update($dados);
            \DB::commit();
            return response()->json([], 200);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($e->getMessage());
            return response()->json([
                'msg' => 'Erro ao salvar as configuraçôes da empresa',
                'erros' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\AvaliacaoFeedback $avaliador
     * @return \Illuminate\Http\Response
     */
    public function destroy(AvaliacaoFeedback $avaliador)
    {
    }


    /**
     * @param Request $request
     * @return array
     */
    public function AvaliadorAssociadoSingle(Request $request)
    {
        $this->authorize('cadastro_avaliacao_vincular_avaliadores');
        $fluxo = $this->fluxoAvaliacao($request);

        $avaliadores = AvaliacaoFeedback::select(['id', 'empresa_id', 'avaliacao_id', 'avaliador_id', 'avaliacao_tipo_id', 'funcionario_id', 'status'])
            ->where('avaliacao_id', $request->avaliacao_id)
            ->where('funcionario_id', $request->funcionario_id)
            ->with('Avaliador:id,nome', 'TipoAvaliador')
            ->OrigemAvaliador()
            ->get()->transform(function ($item) use ($fluxo) {
                $avaliador = $item->Avaliador;
                $fluxoAvaliador = $fluxo->where('id', $item->avaliacao_tipo_id)->first();

                $avaliador->tipo_avaliador_id = $fluxoAvaliador['id'];
                $avaliador->tipo_avaliador_label = $fluxoAvaliador['label'];
                $avaliador->tipo_avaliador_principal = $fluxoAvaliador['principal'];

                return [
                    'id' => $item->id,
                    'avaliador' => $avaliador,
                    'status' => $item->status,
                ];
            });


        return [
            'avaliadores' => $avaliadores,
            'fluxo' => $fluxo
        ];
    }

    /**
     * @param Request $request
     * @return Collection|HigherOrderWhenProxy|mixed
     */
    private function fluxoAvaliacao(Request $request)
    {
        return Avaliacao::fluxoAvaliacao($request->avaliacao_id);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function associar(Request $request)
    {
        $this->authorize('cadastro_avaliacao_vincular_avaliadores');
        $dados = $request->input();
        try {
            \DB::beginTransaction();
            if (count($dados['funcionarios']) == 1) {
                $avaliacaoFuncionario = AvaliacaoFeedback::where('avaliacao_id', $dados['avaliacao_id'])
                    ->where('funcionario_id', $dados['funcionarios'][0])
                    ->where('origem_feedback', AvaliacaoFeedback::ORIGEM_FUNCIONARIO)
                    ->first();

                if ($avaliacaoFuncionario) {
                    if (isset($request['avaliadoresDelete'])) {
                        foreach ($request['avaliadoresDelete'] as $id) {
                            AvaliacaoFeedback::find($id)->delete();
                        }
                    }
                    foreach ($request['avaliadores'] as $item) {
                        if (isset($item['novo'])) {
                            AvaliacaoFeedback::create([
                                'principal' => $item['avaliador']['tipo_avaliador_principal'],
                                'avaliador_id' => $item['avaliador']['id'],
                                'avaliacao_tipo_id' => $item['avaliador']['tipo_avaliador_id'],
                                'avaliacao_id' => $request->avaliacao_id,
                                'funcionario_id' => $dados['funcionarios'][0],
                                'origem_feedback' => AvaliacaoFeedback::ORIGEM_AVALIADOR,
                                'status' => AvaliacaoFeedback::STATUS_AGUARDANDO
                            ]);
                        } else {
                            $avaliadorFeedback = AvaliacaoFeedback::find($item['id']);
                            $avaliadorFeedback->update([
                                'principal' => $item['avaliador']['tipo_avaliador_principal'],
                                'avaliador_id' => $item['avaliador']['id'],
                                'avaliacao_tipo_id' => $item['avaliador']['tipo_avaliador_id'],
                            ]);

                        }
                    }
                } else {
                    AvaliacaoFeedback::create([
                        'avaliacao_id' => $request->avaliacao_id,
                        'funcionario_id' => $dados['funcionarios'][0],
                        'avaliador_id' => $dados['funcionarios'][0],
                        'principal' => false,
                        'origem_feedback' => AvaliacaoFeedback::ORIGEM_FUNCIONARIO,
                        'status' => AvaliacaoFeedback::STATUS_AGUARDANDO
                    ]);
                    foreach ($request['avaliadores'] as $avaliador) {
                        if (isset($avaliador['novo'])) {
                            AvaliacaoFeedback::create(
                                [
                                    'avaliacao_id' => $request->avaliacao_id,
                                    'funcionario_id' => $dados['funcionarios'][0],
                                    'principal' => $avaliador['avaliador']['tipo_avaliador_principal'],
                                    'avaliador_id' => $avaliador['avaliador']['id'],
                                    'avaliacao_tipo_id' => $avaliador['avaliador']['tipo_avaliador_id'],
                                    'origem_feedback' => AvaliacaoFeedback::ORIGEM_AVALIADOR,
                                    'status' => AvaliacaoFeedback::STATUS_AGUARDANDO
                                ]
                            );
                        }
                    }
                }

                \DB::commit();
                return response()->json(['Unica seleção'], 200);

            }

            foreach ($dados['funcionarios'] as $funcionario_id) {
                AvaliacaoFeedback::where('avaliacao_id', $dados['avaliacao_id'])
                    ->where('funcionario_id', $funcionario_id)
                    ->where('origem_feedback', AvaliacaoFeedback::ORIGEM_AVALIADOR)->delete();

                $autoAvaliacao = AvaliacaoFeedback::where('avaliacao_id', $dados['avaliacao_id'])
                    ->where('funcionario_id', $funcionario_id)
                    ->where('origem_feedback', AvaliacaoFeedback::ORIGEM_FUNCIONARIO);

                if ($autoAvaliacao->count() == 0) {
                    AvaliacaoFeedback::create([
                        'avaliacao_id' => $request->avaliacao_id,
                        'avaliador_id' => $funcionario_id,
                        'funcionario_id' => $funcionario_id,
                        'principal' => false,
                        'origem_feedback' => AvaliacaoFeedback::ORIGEM_FUNCIONARIO,
                        'status' => AvaliacaoFeedback::STATUS_AGUARDANDO
                    ]);
                    foreach ($request['avaliadores'] as $avaliador) {
                        AvaliacaoFeedback::create([
                            'avaliacao_id' => $request->avaliacao_id,
                            'funcionario_id' => $funcionario_id,
                            'principal' => $avaliador['avaliador']['tipo_avaliador_principal'],
                            'avaliador_id' => $avaliador['avaliador']['id'],
                            'avaliacao_tipo_id' => $avaliador['avaliador']['tipo_avaliador_id'],
                            'origem_feedback' => AvaliacaoFeedback::ORIGEM_AVALIADOR,
                            'status' => AvaliacaoFeedback::STATUS_AGUARDANDO
                        ]);
                    }
                } else {
                    foreach ($request['avaliadores'] as $avaliador) {
                        AvaliacaoFeedback::create(
                            [
                                'avaliacao_id' => $request->avaliacao_id,
                                'funcionario_id' => $funcionario_id,
                                'principal' => $avaliador['avaliador']['tipo_avaliador_principal'],
                                'avaliador_id' => $avaliador['avaliador']['id'],
                                'avaliacao_tipo_id' => $avaliador['avaliador']['tipo_avaliador_id'],
                                'origem_feedback' => AvaliacaoFeedback::ORIGEM_AVALIADOR,
                                'status' => AvaliacaoFeedback::STATUS_AGUARDANDO
                            ]
                        );
                    }
                }

                \DB::commit();
                return response()->json(['Multi seleção'], 200);
            }
        } catch
        (\Exception $e) {
            \DB::rollBack();
            \Log::error($e->getMessage());
            return response()->json([
                'msg' => 'Erro ao associar o avaliador',
                'erros' => $e->getMessage()
            ], 400);
        }
    }

    public function atualizarFuncionarios(Request $request)
    {
        $this->authorize('cadastro_avaliacao_vincular_avaliadores');

        $fluxo = $this->fluxoAvaliacao($request);

        $resultado = User::select(['id', 'nome', 'login', 'tipo', 'ativo'])
            ->TiposGerenciais()
            ->whereEmpresaId(auth()->user()->empresa_id)
            ->whereAtivo(true)
            ->whereDoesntHave('Curriculo.FeedBack.Demissao') // não pode ter demissão
            ->with('Curriculo.FeedBack.Admissao')
            ->with('Avaliadores', function ($query) use ($request) {
                $query->where('avaliacao_id', $request->avaliacao_id)
                    ->with('Avaliador:id,nome');
            })->orderBy('nome');

        $porPagina = $request->get('porPagina');

        if ($request->filled('campoBusca')) {
            $busca = $request->get('campoBusca');
            $resultado->where('nome', 'like', '%' . $busca . '%');
        }

        if ($request->filled('campoVinculados')) {
            if ($request->campoVinculados) {
                $resultado->whereHas('Avaliadores', function ($query) use ($request) {
                    $query->where('avaliacao_id', $request->avaliacao_id);
                });
            } else {
                $resultado->doesntHave('Avaliadores');
            }
        }

        $resultado = $resultado->paginate($porPagina);

        $funcionarios = collect($resultado->items())->map(function ($item) use ($fluxo) {
            $avaliadores = $item->Avaliadores->map(function ($avaliador) use ($fluxo) {
                $fluxoAvaliador = $fluxo->where('id', $avaliador->avaliacao_tipo_id)->first();
                $avaliador->tipo_avaliador_id = $fluxoAvaliador['id'];
                $avaliador->tipo_avaliador_label = $fluxoAvaliador['label'];
                $avaliador->tipo_avaliador_principal = $fluxoAvaliador['principal'];
                return $avaliador;
            });

            // Reorganizar a coleção para que os avaliadores com 'principal' sejam os primeiros
            $avaliadores = $avaliadores->sortByDesc('tipo_avaliador_principal')->values();

            $item->avaliadores = $avaliadores;
            return $item;
        });


        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'funcionarios' => $funcionarios,
                'fluxo' => $fluxo
            ],
        ]);
    }
}
