<?php

namespace App\Http\Controllers;

use App\Exports\ModeloRowsExport;
use App\Jobs\JobExportaExcel;
use App\Jobs\Movimentacao\MudaCargoPrevista\JobMudaCargoPrevistaAprovar;
use App\Jobs\Movimentacao\MudaCargoPrevista\JobMudaCargoPrevistaAprovarRH;
use App\Models\Arquivo;
use App\Models\MudaCargoPrevista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class MudaCargoPrevistaController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
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
                'msg' => 'Erro ao Solicitar Demissão',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

            try {
                DB::beginTransaction();
                $mudaCargoPrevista = MudaCargoPrevista::create($dados);
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
                            $mudaCargoPrevista->Anexos()->attach($arquivo->id);
                        }
                    }
                }
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Mudança de Cargo:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\MudaCargoPrevista $mudaCargoPrevista
     * @return MudaCargoPrevista|\Illuminate\Http\Response
     */
    public function edit(MudaCargoPrevista $mudaCargoPrevista)
    {
        $mudaCargoPrevista->autocomplete_label_colaborador = $mudaCargoPrevista->Colaborador ? $mudaCargoPrevista->Colaborador->nome : '';
        $mudaCargoPrevista->autocomplete_label_colaborador_anterior = $mudaCargoPrevista->Colaborador ? $mudaCargoPrevista->Colaborador->nome : '';

        $mudaCargoPrevista->autocomplete_label_gestor_modal = $mudaCargoPrevista->GestorAprovacao ? $mudaCargoPrevista->GestorAprovacao->nome : '';
        $mudaCargoPrevista->autocomplete_label_gestor_modal_anterior = $mudaCargoPrevista->GestorAprovacao ? $mudaCargoPrevista->GestorAprovacao->nome : '';

        $mudaCargoPrevista->autocomplete_label_cargoanterior = $mudaCargoPrevista->CargoAnterior ? $mudaCargoPrevista->CargoAnterior->nome : '';
        $mudaCargoPrevista->autocomplete_label_cargoanterior_anterior = $mudaCargoPrevista->CargoAnterior ? $mudaCargoPrevista->CargoAnterior->nome : '';

        $mudaCargoPrevista->autocomplete_label_novo_cargo = $mudaCargoPrevista->NovoCargo ? $mudaCargoPrevista->NovoCargo->nome : '';
        $mudaCargoPrevista->autocomplete_label_novo_cargo_anterior = $mudaCargoPrevista->NovoCargo ? $mudaCargoPrevista->NovoCargo->nome : '';
        $mudaCargoPrevista->anexosDel = [];
        $mudaCargoPrevista->load('Anexos');
        return $mudaCargoPrevista;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\MudaCargoPrevista $mudaCargoPrevista
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, MudaCargoPrevista $mudaCargoPrevista)
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
                'msg' => 'Erro ao Solicitar Demissão',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $mudaCargoPrevista->update($dados);
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
                            $mudaCargoPrevista->Anexos()->attach($arquivo->id);
                        }
                    }
                }
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Mudança de Cargo:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\MudaCargoPrevista $mudaCargoPrevista
     * @return \Illuminate\Http\Response
     */
    public function destroy(MudaCargoPrevista $mudaCargoPrevista)
    {
        //
    }

    public function aprovarRH(Request $request, MudaCargoPrevista $mudaCargoPrevista)
    {
        $this->authorize('rh_aprova_movimentacao');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $mudaCargoPrevista->update([
                'user_rh_id' => auth()->id(),
                'resposta_rh' => $dados['resposta_rh'],
                'obs_rh' => $dados['obs_rh'],
                'data_aprovacao_rh' => (new DataHora())->dataHoraInsert(),
            ]);

            DB::commit();

            JobMudaCargoPrevistaAprovarRH::dispatch($mudaCargoPrevista);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar solicitação RH:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    public function aprovar(Request $request, MudaCargoPrevista $mudaCargoPrevista)
    {
        $this->authorize('privilegio_aprovar_por_gestor');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $mudaCargoPrevista->update([
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
                        $mudaCargoPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }
            DB::commit();
            JobMudaCargoPrevistaAprovar::dispatch($mudaCargoPrevista);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar Mudança de Cargo:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->pages);

        $periodo = MudaCargoPrevista::all();
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'periodo' => $periodo,
                'aprovar_por_gestor' => auth()->user()->can('privilegio_aprovar_por_gestor'),
            ]
        ]);
    }

    public function filtro(Request $request)
    {
        $resultado = MudaCargoPrevista::with(
            'CentroCusto',
            'CargoAnterior',
            'NovoCargo',
            'UserCadastrou:id,nome',
            'Colaborador:id,nome,login,tipo,ativo','GestorAprovacao:id,nome','UserAprovacao:id,nome');

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

        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == "aberto" ? null : $request->campoStatus;
            $resultado->whereStatusAprovacao($status);
        }

        if (!auth()->user()->can('privilegio_gestao_rh')){
            $resultado->whereUserId(auth()->user()->id)->orWhere('gestor_id', auth()->user()->id);
        }

        return $resultado->orderByDesc('created_at');
    }

    public function export(Request $request)
    {
        $resultado = $this->filtro($request)->get();

        $head = [
            "Quem Solicitou",
            "Data da Solicitação",
            "Centro de Custo",
            "Colaborador",
            "Cargo Anterior",
            "Salário Anterior",
            "Cargo Novo",
            "Salário Novo",
            "Gestor Aprovação",
            "Observação",
            "Status",
            "Quem Aprovou/Reprovou",
            "Data da Aprovação/Reprovação",
            'Observação Aprovação/Reprovação',
        ];

        $rows = [];

        foreach ($resultado as $row) {
            $rows[] = [
                $row->UserCadastrou->nome,
                (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5),
                $row->CentroCusto->label,
                $row->Colaborador->nome,
                $row->CargoAnterior->nome,
                $row->salario_anterior_format,
                $row->NovoCargo->nome,
                $row->novo_salario_format,
                $row->GestorAprovacao->nome,
                $row->obs,
                $row->status_aprovacao ? $row->status_aprovacao : "aberto",
                $row->QuemAprovou ? $row->QuemAprovou->nome : "aguardando",
                $row->data_aprovacao ? (new DataHora($row->data_aprovacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao))->horaCompleta(), 0, 5) : '',
                $row->obs_aprovacao,
            ];
        }

        $nameArquivo = "muda_cargo_prevista" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Muda Cargo - Prevista", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }
    public function atualizacaoStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            foreach ($request->selecionados[0] as $selecionado) {

                $dados = MudaCargoPrevista::find($selecionado);

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
}
