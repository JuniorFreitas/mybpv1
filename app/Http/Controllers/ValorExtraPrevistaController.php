<?php

namespace App\Http\Controllers;

use App\Exports\ModeloRowsExport;
use App\Jobs\Movimentacao\ValorExtraPrevista\JobValorExtraPrevistaAprovar;
use App\Jobs\Movimentacao\ValorExtraPrevista\JobValorExtraPrevistaAprovarRH;
use App\Models\ValorExtraPrevista;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;

class ValorExtraPrevistaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $dados = $request->input();

        $dados['user_id'] = auth()->user()->id;
        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'tipo' => 'required',
                'periodo_dias' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Valor Extra',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                ValorExtraPrevista::create($dados);
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Solicitação de Valor Extra:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\ValorExtraPrevista $valorExtraPrevista
     * @return \Illuminate\Http\Response
     */
    public function show(ValorExtraPrevista $valorExtraPrevista)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\ValorExtraPrevista $valorExtraPrevista
     * @return ValorExtraPrevista|\Illuminate\Http\Response
     */
    public function edit(ValorExtraPrevista $valorExtraPrevista)
    {
        $valorExtraPrevista->autocomplete_label_colaborador = $valorExtraPrevista->Colaborador ? $valorExtraPrevista->Colaborador->nome : '';
        $valorExtraPrevista->autocomplete_label_colaborador_anterior = $valorExtraPrevista->Colaborador ? $valorExtraPrevista->Colaborador->nome : '';

        $valorExtraPrevista->autocomplete_label_gestor_modal = $valorExtraPrevista->GestorAprovacao ? $valorExtraPrevista->GestorAprovacao->nome : '';
        $valorExtraPrevista->autocomplete_label_gestor_modal_anterior = $valorExtraPrevista->GestorAprovacao ? $valorExtraPrevista->GestorAprovacao->nome : '';

        return $valorExtraPrevista;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\ValorExtraPrevista $valorExtraPrevista
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, ValorExtraPrevista $valorExtraPrevista)
    {
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados,
            [
                'centro_custo_id' => 'required',
                'colaborador_id' => 'required',
                'tipo' => 'required',
                'periodo_dias' => 'required',
            ]
        );
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Solicitar Valor Extra',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $valorExtraPrevista->update($dados);
                DB::commit();
                return response()->json('', 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "erro ao salvar Solicitação de Valor Extra:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\ValorExtraPrevista $valorExtraPrevista
     * @return \Illuminate\Http\Response
     */
    public function destroy(ValorExtraPrevista $valorExtraPrevista)
    {
        //
    }

    public function aprovarRH(Request $request, ValorExtraPrevista $valorExtraPrevista)
    {
        $this->authorize('rh_aprova_movimentacao');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $valorExtraPrevista->update([
                'user_rh_id' => auth()->id(),
                'resposta_rh' => $dados['resposta_rh'],
                'obs_rh' => $dados['obs_rh'],
                'data_aprovacao_rh' => (new DataHora())->dataHoraInsert(),
            ]);

            DB::commit();

            JobValorExtraPrevistaAprovarRH::dispatch($valorExtraPrevista);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar solicitação RH:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    public function aprovar(Request $request, ValorExtraPrevista $valorExtraPrevista)
    {
        $this->authorize('aprovar_por_gestor');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $valorExtraPrevista->update([
                'user_aprovacao_id' => auth()->id(),
                'data_aprovacao' => (new DataHora())->dataHoraInsert(),
                'obs_aprovacao' => $dados['obs_aprovacao'],
                'status_aprovacao' => $dados['status_aprovacao'],
            ]);
            DB::commit();


            JobValorExtraPrevistaAprovar::dispatch($valorExtraPrevista);

            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error ao aprovar VALOR EXTRA:  {$e->getFile()}, {$e->getMessage()}, {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => $msg], 400);
//            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }


    public function atualizar(Request $request)
    {
        $resultado = ValorExtraPrevista::with(
            'CentroCusto',
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

        if (!auth()->user()->can('gestao_rh')){
            $resultado->whereUserId(auth()->user()->id)->orWhere('gestor_id', auth()->user()->id);
        }

        $resultado = $resultado->orderByDesc('created_at')->paginate($request->pages);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'aprovar_por_gestor' => auth()->user()->can('aprovar_por_gestor'),
            ]
        ]);
    }

    public function exportaExcel(Request $request)
    {
        $resultado = $this->filtro($request)->get();

        $head = [
            "ID",
            "QUEM SOLICITOU",
            "SOLICITAÇÃO",
            "EMPRESA",
            "CENTRO DE CUSTO",
            "COLABORADOR",
            "TIPO",
            "PERÍODO EM DIAS",
            "GESTOR APROVAÇÃO",
            "OBSERVAÇÃO",

            "STATUS",
            "QUEM APROVOU/REPROVOU",
            "DATA DA APROVAÇÃO/REPROVAÇÃO",
            'OBSERVAÇÃO APROVAÇÃO/REPROVAÇÃO',
        ];

        $rows = [];

        foreach ($resultado as $row) {
            $rows[] = [
                $row->id,
                $row->UserCadastrou->nome,
                (new DataHora($row->created_at))->dataCompleta() . ' ' . substr((new DataHora($row->created_at))->horaCompleta(), 0, 5),
                $row->Cliente ? $row->Cliente->razao_social : $row->Cliente->nome,
                $row->CentroCusto->label,
                $row->Colaborador->nome,
                $row->tipo,
                $row->periodo_dias,
                $row->GestorAprovacao->nome,
                $row->observacao,

                $row->status_aprovacao ? $row->status_aprovacao : "aberto",
                $row->QuemAprovou ? $row->QuemAprovou->nome : "aguardando",
                (new DataHora($row->data_aprovacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao))->horaCompleta(), 0, 5),
                $row->obs_aprovacao,
            ];
        }

        return \Excel::download(new ModeloRowsExport($head, $rows), 'Liderança de pessoal e valor extra - ' . (new DataHora())->nomeUnico() . '.xlsx');

    }
    public function atualizacaoStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            foreach ($request->selecionados[0] as $selecionado) {

                $dados = ValorExtraPrevista::find($selecionado);

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
