<?php

namespace App\Http\Controllers;

use App\Jobs\JobExportaExcel;
use App\Jobs\Movimentacao\AdmissaoPrevista\JobAdmissaoPrevistaAprovar;
use App\Jobs\Movimentacao\AdmissaoPrevista\JobAdmissaoPrevistaAprovarRH;
use App\Models\AdmissoesPrevista;
use App\Models\Arquivo;
use App\Models\DemissaoPrevista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class AdmissoesPrevistaController extends Controller
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
        $dados['salario'] = $dados['salario_format'];
        $dados['user_id'] = auth()->user()->id;

        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'tipo_contrato' => 'required',
                'cargo_id' => 'required',
                'salario_format' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Admissão',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $admPrevista = AdmissoesPrevista::create($dados);
                if (isset($dados['anexos'])) {
                    foreach ($dados['anexos'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $admPrevista->Anexos()->attach($arquivo->id);
                        }
                    }
                }
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Solicitação de Admissão:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\AdmissoesPrevista $admissoesPrevista
     * @return AdmissoesPrevista|\Illuminate\Http\Response
     */
    public function edit(AdmissoesPrevista $admissoesPrevista)
    {
        $admissoesPrevista->autocomplete_label_gestor_modal = $admissoesPrevista->GestorAprovacao ? $admissoesPrevista->GestorAprovacao->nome : '';
        $admissoesPrevista->autocomplete_label_gestor_modal_anterior = $admissoesPrevista->GestorAprovacao ? $admissoesPrevista->GestorAprovacao->nome : '';

        $admissoesPrevista->autocomplete_label_cargo = $admissoesPrevista->Cargo ? $admissoesPrevista->Cargo->nome : '';
        $admissoesPrevista->autocomplete_label_cargo_anterior = $admissoesPrevista->Cargo ? $admissoesPrevista->Cargo->nome : '';

        $admissoesPrevista->anexosDel = [];
        $admissoesPrevista->load('Anexos');
        return $admissoesPrevista;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\AdmissoesPrevista $admissoesPrevista
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, AdmissoesPrevista $admissoesPrevista)
    {
        $dados = $request->input();
        $dados['salario'] = $dados['salario_format'];

        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'tipo_contrato' => 'required',
                'cargo_id' => 'required',
                'salario_format' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Admissão',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            DB::beginTransaction();
            $admissoesPrevista->update($dados);
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
                        $admissoesPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }
            DB::commit();
            return response()->json('', 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "erro ao salvar Solicitação de Admissão:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function aprovar(Request $request, AdmissoesPrevista $admissoesPrevista)
    {
        $this->authorize('privilegio__aprovar_por_gestor');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $admissoesPrevista->update([
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
                        $admissoesPrevista->Anexos()->attach($arquivo->id);
                    }
                }
            }

            DB::commit();

            JobAdmissaoPrevistaAprovar::dispatch($admissoesPrevista);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar ADMISSÃO PREVISTA:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    public function aprovarRH(Request $request, AdmissoesPrevista $admissoesPrevista)
    {
        $this->authorize('rh_aprova_movimentacao');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $admissoesPrevista->update([
                'user_rh_id' => auth()->id(),
                'resposta_rh' => $dados['resposta_rh'],
                'obs_rh' => $dados['obs_rh'],
                'data_aprovacao_rh' => (new DataHora())->dataHoraInsert(),
            ]);

            DB::commit();

            JobAdmissaoPrevistaAprovarRH::dispatch($admissoesPrevista);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar solicitação RH:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->pages);

        $periodo = AdmissoesPrevista::all();

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

    public function atualizacaoStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            foreach ($request->selecionados[0] as $selecionado) {

                $dados = AdmissoesPrevista::find($selecionado);

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

    public function filtro(Request $request)
    {
        $resultado = AdmissoesPrevista::with(
            'Cargo',
            'CentroCusto',
            'UserCadastrou:id,nome',
            'Colaborador:id,nome,login,tipo,ativo', 'GestorAprovacao:id,nome', 'UserAprovacao:id,nome');

        $filtroPeriodo = $request->filtroPeriodo == 'true' ? true : false;

        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0], ' 00:00:00');
            $dataFim = new DataHora($periodo[1], ' 23:59:59');
            $resultado->where('created_at', '>=', $dataInicio->dataHoraInsert())->where('created_at', '<=', $dataFim->dataHoraInsert());
        }

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Cargo', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->campoBusca . '%')
                    ->orWhere('id', $request->campoBusca);
            });
        }

        if ($request->filled('campoStatus')) {
            $status = $request->campoStatus == "aberto" ? null : $request->campoStatus;
            $resultado->whereStatusAprovacao($status);
        }

        if (!auth()->user()->can('privilegio_gestao_rh')) {
            $resultado->whereUserId(auth()->user()->id)->orWhere('gestor_id', auth()->user()->id);
        }

        return $resultado->orderByDesc('created_at');

    }

    //Excel
    public function export(Request $request)
    {

        $resultado = $this->filtro($request)->get();
        $head = [
            "Quem Solicitou",
            "Cargo",
            "Data da Solicitação",
            "Centro de Custo",
            "Tipo de Contrato",
            "Data da Admissão",
            "Observação",
            "Salário",
            "Gestor Aprovação",
            "Quem Aprovou/Reprovou",
            "Data da Aprovação/Reprovação",
            "Status"
        ];

        $rows = [];

        foreach ($resultado as $row) {
            $rows[] = [
                $row->UserCadastrou->nome,
                $row->Cargo->nome,
                (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5),
                $row->CentroCusto->label,
                $row->tipo_contrato,
                (new DataHora($row->data_admissao))->dataCompleta(),
                $row->obs,
                $row->salario_format,
                $row->GestorAprovacao->nome,
                $row->UserAprovacao ? $row->UserAprovacao->nome : "aguardando",
                $row->data_aprovacao ? (new DataHora($row->data_aprovacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao))->horaCompleta(), 0, 5) : '',
                $row->status_aprovacao ? $row->status_aprovacao : "aberto",
            ];
        }

        $nameArquivo = "admissao_prevista" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Admissão - Prevista", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);

    }
}
