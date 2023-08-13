<?php

namespace App\Http\Controllers;


use App\Jobs\Movimentacao\MudaIntermitenteFixoPrevista\JobMudaIntermitenteFixoPrevistaAprovar;
use App\Jobs\Movimentacao\MudaIntermitenteFixoPrevista\JobMudaIntermitenteFixoPrevistaAprovarRH;
use App\Jobs\Movimentacao\MudaIntermitenteFixoPrevista\JobMudaIntermitenteFixoPrevistaExportaExcel;
use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\IntermitenteFixoPrevista;
use App\Models\VagasAbertas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class IntermitenteFixoPrevistaController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $dados = $request->input();
        $dados['salario_anterior'] = $dados['salario_anterior_format'];
        $dados['novo_salario'] = $dados['novo_salario_format'];
        $dados['user_id'] = auth()->user()->id;
        $dados['centro_custo_filial_id'] = $dados['filial'] ? $dados['centro_custo_filial_id'] : null;

        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'centro_custo_filial_id' => 'required_if:filial,true',
                'colaborador_id' => 'required',
                'cargo_anterior_id' => 'required',
                'salario_anterior_format' => 'required',
                'novo_cargo_id' => 'required',
                'novo_salario_format' => 'required',
                'anterior_vaga_aberta_id' => 'required',
                'nova_vaga_aberta_id' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Mudança Intermitente Fixo',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                $intermitenteFixoPrevista = IntermitenteFixoPrevista::create($dados);
                if (isset($dados['anexos'])) {
                    foreach ($dados['anexos'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $intermitenteFixoPrevista->Anexos()->attach($arquivo->id);
                        }
                    }
                }
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar  Mudança Intermitente Fixo:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\IntermitenteFixoPrevista $intermitenteFixoPrevista
     * @return \Illuminate\Http\Response
     */
    public function edit(IntermitenteFixoPrevista $intermitenteFixoPrevista)
    {
        $intermitenteFixoPrevista->autocomplete_label_colaborador = $intermitenteFixoPrevista->Colaborador ? $intermitenteFixoPrevista->Colaborador->nome : '';
        $intermitenteFixoPrevista->autocomplete_label_colaborador_anterior = $intermitenteFixoPrevista->Colaborador ? $intermitenteFixoPrevista->Colaborador->nome : '';

        $intermitenteFixoPrevista->autocomplete_label_cargoanterior = $intermitenteFixoPrevista->CargoAnterior ? $intermitenteFixoPrevista->CargoAnterior->nome : '';
        $intermitenteFixoPrevista->autocomplete_label_cargoanterior_anterior = $intermitenteFixoPrevista->CargoAnterior ? $intermitenteFixoPrevista->CargoAnterior->nome : '';

        $intermitenteFixoPrevista->autocomplete_label_novo_cargo = $intermitenteFixoPrevista->NovoCargo ? $intermitenteFixoPrevista->NovoCargo->nome : '';
        $intermitenteFixoPrevista->autocomplete_label_novo_cargo_anterior = $intermitenteFixoPrevista->NovoCargo ? $intermitenteFixoPrevista->NovoCargo->nome : '';
        $intermitenteFixoPrevista->autocomplete_label_vaga_anterior = $intermitenteFixoPrevista->VagaAbertaAnterior ? $intermitenteFixoPrevista->VagaAbertaAnterior->titulo : '';
        $intermitenteFixoPrevista->autocomplete_label_vaga_nova = $intermitenteFixoPrevista->VagaAbertaNova ? $intermitenteFixoPrevista->VagaAbertaNova->titulo : '';

        $intermitenteFixoPrevista->status_aprovacao = $intermitenteFixoPrevista->status_aprovacao ?: '';
        $intermitenteFixoPrevista->status_aprovacao_rh = $intermitenteFixoPrevista->status_aprovacao_rh ?: '';

        $intermitenteFixoPrevista->autocomplete_label_gestor_modal = $intermitenteFixoPrevista->GestorAprovacao ? $intermitenteFixoPrevista->GestorAprovacao->nome : '';
        $intermitenteFixoPrevista->autocomplete_label_gestor_modal_anterior = $intermitenteFixoPrevista->GestorAprovacao ? $intermitenteFixoPrevista->GestorAprovacao->nome : '';
        $intermitenteFixoPrevista->user_aprovacao = $intermitenteFixoPrevista->UserAprovacao;
        $intermitenteFixoPrevista->anexosDel = [];
        $intermitenteFixoPrevista->load('Anexos');
        return $intermitenteFixoPrevista;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\IntermitenteFixoPrevista $intermitenteFixoPrevista
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, IntermitenteFixoPrevista $intermitenteFixoPrevista)
    {
        $dados = $request->input();
        $dados['salario_anterior'] = $dados['salario_anterior_format'];
        $dados['novo_salario'] = $dados['novo_salario_format'];
        $dados['user_id'] = auth()->user()->id;


        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'cargo_anterior_id' => 'required',
                'salario_anterior_format' => 'required',
                'novo_cargo_id' => 'required',
                'novo_salario_format' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar  Mudança Intermitente Fixo',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            $intermitenteFixoPrevista->update($dados);
            if (isset($dados['anexosDel'])) {
                foreach ($dados['anexosDel'] as $id_anexo) {
                    $arquivo = Arquivo::find($id_anexo);
                    $arquivo->excluir();
                }
            }

            if (isset($dados['anexos'])) {
                foreach ($dados['anexos'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $intermitenteFixoPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }
            DB::commit();
            return response()->json('', 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao salvar  Mudança Intermitente Fixo:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function aprovar(Request $request, IntermitenteFixoPrevista $intermitenteFixoPrevista)
    {
        $this->authorize('privilegio_aprovar_por_gestor');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $intermitenteFixoPrevista->update([
                'user_aprovacao_id' => auth()->id(),
                'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                'obs_aprovacao' => $dados['obs_aprovacao'],
                'status_aprovacao' => $dados['status_aprovacao'],
            ]);
            if (isset($dados['anexosDel'])) {
                foreach ($dados['anexosDel'] as $id_anexo) {
                    $arquivo = Arquivo::find($id_anexo);
                    $arquivo->excluir();
                }
            }

            if (isset($dados['anexos'])) {
                foreach ($dados['anexos'] as $index => $anexo) {
                    $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                    if ($arquivo) {
                        $arquivo->temporario = false;
                        $arquivo->chave = '';
                        $arquivo->save();
                        $intermitenteFixoPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }
            DB::commit();
            JobMudaIntermitenteFixoPrevistaAprovar::dispatch($intermitenteFixoPrevista);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar Intermitente Fixo Prevista:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => $msg], 400);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    public function aprovarRH(Request $request, IntermitenteFixoPrevista $intermitenteFixoPrevista)
    {
        $this->authorize('privilegio_aprovar_por_rh');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $intermitenteFixoPrevista->update([
                'rh_aprovacao_id' => auth()->id(),
                'status_aprovacao_rh' => $dados['status_aprovacao_rh'],
                'obs_rh' => $dados['obs_rh'],
                'data_aprovacao_rh' => (new DataHora())->dataHoraInsert(),
            ]);

            $admissao_id = $intermitenteFixoPrevista->Colaborador->Curriculo->FeedBack->Admissao->id;
            Admissao::find($admissao_id)->update([
                'centro_custo_id' => $dados['centro_custo_id'],
                'filial' => $dados['filial'],
                'centro_custo_filial_id' => $dados['centro_custo_filial_id'],
                'cargo' => $intermitenteFixoPrevista->VagaAbertaNova->Vaga->nome,
                'salario' => $dados['novo_salario']
            ]);
            DB::commit();

            JobMudaIntermitenteFixoPrevistaAprovarRH::dispatch($intermitenteFixoPrevista);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar solicitação RH:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => $msg], 400);
        }

    }

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'aprovar_por_gestor' => auth()->user()->can('privilegio_aprovar_por_gestor'),
                'aprovar_por_rh' => auth()->user()->can('privilegio_aprovar_por_rh'),
            ]
        ]);
    }

    public function filtro(Request $request)
    {
        $resultado = IntermitenteFixoPrevista::with(
            'CentroCusto',
            'CargoAnterior',
            'CentroCustoFilial',
            'AreaEtiqueta',
            'NovoCargo',
            'VagaAbertaAnterior',
            'VagaAbertaNova',
            'UserAprovacao:id,nome',
            'Solicitante:id,nome',
            'GestorAprovacao:id,nome',
            'RhAprovacao:id,nome',
            'QuemDeletou:id,nome',
            'Colaborador:id,nome,login,tipo,ativo', 'GestorAprovacao:id,nome', 'UserAprovacao:id,nome');

        $filtroPeriodo = $request->filtroPeriodo == 'true' ? true : false;

        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0]);
            $dataFim = new DataHora($periodo[1]);
            $resultado->where('created_at', '>=', $dataInicio->dataInsert() . ' 00:00:00')->where('created_at', '<=', $dataFim->dataInsert() . ' 23:59:59');
        }

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Colaborador', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoStatusAprovacao')) {
            $status = $request->campoStatusAprovacao;
            if ($request->campoStatusAprovacao == "aberto") {
                $resultado->whereNull('status_aprovacao');
            } elseif ($request->campoStatusAprovacao == "aprovado_gestor") {
                $resultado->where('status_aprovacao', IntermitenteFixoPrevista::STATUS_APROVADO)->whereNull('status_aprovacao_rh');
            } elseif ($request->campoStatusAprovacao == "aprovado_rh") {
                $resultado->where('status_aprovacao_rh', IntermitenteFixoPrevista::STATUS_APROVADO);
            } else {
                $resultado->whereStatusAprovacao(IntermitenteFixoPrevista::STATUS_REPROVADO)->orWhere('status_aprovacao_rh', IntermitenteFixoPrevista::STATUS_REPROVADO);
            }
        }

        if (!auth()->user()->can('privilegio_gestao_rh')) {
            $resultado->whereUserId(auth()->user()->id)->orWhere('gestor_id', auth()->user()->id);
        }

        return $resultado->orderByDesc('created_at');
    }

    public function export(Request $request)
    {
        JobMudaIntermitenteFixoPrevistaExportaExcel::dispatch(auth()->user(),$this->filtro($request));
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }

    public function atualizacaoStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            foreach ($request->selecionados[0] as $selecionado) {

                $dados = IntermitenteFixoPrevista::find($selecionado);

                $dados->update([
                    'user_aprovacao_id' => auth()->id(),
                    'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                    'obs_aprovacao' => $request->obs_aprovacao,
                    'status_aprovacao' => $request->status_aprovacao,
                ]);

                DB::commit();
            }
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar solicitação em massa:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    function firstOrCreateVagaAberta($vaga_id, $municipio_id, $empresa_id, $titulo, $descricao = '', $ativo_sistema = true, $ativo = true)
    {
        $vaga = VagasAbertas::withoutGlobalScopes()->firstOrCreate([
            'vaga_id' => $vaga_id,
            'municipio_id' => $municipio_id,
            'empresa_id' => $empresa_id,
            'titulo' => $titulo,
            'descricao' => $descricao,
            'ativo_sistema' => $ativo_sistema,
            'ativo' => $ativo
        ]);

        return $vaga;
    }
}
