<?php

namespace App\Http\Controllers;

use App\Models\AreaEtiqueta;
use App\Models\Arquivo;
use App\Models\Cliente;
use App\Models\Intermitente;
use App\Models\IntermitenteProrrogacao;
use App\Models\IntermitenteTipo;
use App\Models\User;
use DB;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class IntermitenteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Foundation\Application|\Illuminate\Http\Response|\Illuminate\View\View
     */
    public function index()
    {
        return view('g.admissao.apontamento.intermitente.index');
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
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('intermitente');
        $dados = $request->input();
        $dados['user_lancamento_id'] = auth()->id();
//        $dados['data_lancamento'] = (new DataHora($dados['data_lancamento'] . ' ' . date('H:m:s')))->dataHoraInsert();

        $dados['range_convocacao'] = explode(' até ', $dados['data_lancamento']);
        $dados['data_lancamento'] = (new DataHora($dados['range_convocacao'][0] . ' ' . date('H:m:s')))->dataHoraInsert(); // data concocação
        $dados['encerramento_previsto'] = (new DataHora($dados['range_convocacao'][1]))->dataInsert();; // data fim convocacao


        $dadosValidados = \Validator::make($dados, [
            'tipo_id' => 'required',
            'feedback_id' => 'required'
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar Informações',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $dados['tipo_id'] = $dados['tipo_id'] > 0 ? $dados['tipo_id'] : null;
                $dados['area_id'] = $dados['area_id'] > 0 ? $dados['area_id'] : null;
                $intermitente = Intermitente::create($dados);

                if (isset($dados['anexosDel'])) {
                    foreach ($dados['anexosDel'] as $id_anexo) {
                        $arquivo = Arquivo::find($id_anexo);
                        $arquivo->excluir();
                    }
                }

                // inseri uma nova foto de anexo
                if (isset($dados['anexos'])) {
                    foreach ($dados['anexos'] as $index => $anexo) {
                        $arquivo = Arquivo::whereChave($anexo['chave'])->whereId($anexo['id'])->first();
                        if ($arquivo) {
                            $arquivo->temporario = false;
                            $arquivo->chave = '';
                            $arquivo->save();
                            $intermitente->Anexos()->attach($arquivo->id);
                        }
                    }
                }

                DB::commit();
                return response()->json([$intermitente->load('Anexos')], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error STORE Intermitente:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
                //return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    public function storeTipo(Request $request)
    {
        $this->authorize('intermitente');
        $dados = $request->input();
        $dados['ativo'] = true;
        $dadosValidados = \Validator::make($dados, [
            'label' => 'required'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Cadastrar',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                IntermitenteTipo::create($dados);

                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error EM TIPO DE INTERMITENTE:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()}";
                \Log::debug($msg);
                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }

    public function storeProrrogacao(Request $request)
    {
        $this->authorize('intermitente');
        $dados = $request->input();
        $dadosValidados = \Validator::make($dados, [
            'prorrogacao*data_inicio' => 'required',
            'prorrogacao*data_fim' => 'required',
            'prorrogacao*solicitante' => 'required',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Cadastrar Prorrogação',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();

                foreach ($dados['prorrogacao'] as $p) {
                    $info = [
                        'intermitente_id' => $dados['intermitente_id'],
                        'data_inicio' => $p['data_inicio'],
                        'data_fim' => $p['data_fim'],
                        'solicitante' => $p['solicitante'],
                    ];
                    IntermitenteProrrogacao::create($info);
                }


                DB::commit();
                return response()->json([], 201);
            } catch (\Exception $e) {
                DB::rollback();
                $msg = "error EM STORE PRORROGAÇÃO DE INTERMITENTE:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()}";
                \Log::debug($msg);
                return response()->json(['msg' => $msg], 400);
//                return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
            }
        }
    }


    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Intermitente|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|\Illuminate\Http\Response|object
     */
    public function edit($id)
    {
        $intermitente = Intermitente::whereId($id)->first();

        $intermitente->autocomplete_label_colaborador = "{$intermitente->Colaborador->Curriculo->nome} - {$intermitente->Colaborador->VagaAberta->VagaSelecionada->nome} - {$intermitente->Colaborador->VagaAberta->Municipio->uf}";
        $intermitente->autocomplete_label_colaborador_anterior = $intermitente->autocomplete_label_colaborador;
        $intermitente->tipo_id = is_null($intermitente->tipo_id) ? 0 : $intermitente->tipo_id;
        $intermitente->area_id = is_null($intermitente->area_id) ? 0 : $intermitente->area_id;
        $intermitente->status_aprovacao = $intermitente->status;
        $intermitente->treinamentos = $intermitente->Colaborador->Treinamentos ? $intermitente->Colaborador->Treinamentos->Vencimentos : [];

        return $intermitente->load('Anexos', 'Tipo', 'Area','Prorrogacao');
    }

    public function editProrrogacao($id)
    {
        return IntermitenteProrrogacao::where('intermitente_id', $id)->get();
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function encerrarConvocacao(Request $request)
    {
        $this->authorize('intermitente');
        $dados = $request->input();
        $dados['user_aprovacao_id'] = auth()->id();
        $dados['status'] = 'encerrado';
        $dados['data_aprovacao'] = (new DataHora())->dataHoraInsert();

        try {
            DB::beginTransaction();

            $intermitente = Intermitente::whereId($dados['id'])->first();

            $intermitente->update([
                'user_aprovacao_id' => $dados['user_aprovacao_id'],
                'data_aprovacao' => $dados['data_aprovacao'],
                'status' => $dados['status'],
                'devolve_epi' =>$dados['devolve_epi'],
                'devolve_cracha' =>$dados['devolve_cracha'],
            ]);
            DB::commit();
            return response()->json([$intermitente], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error UPDATE INTERMITENTE:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => $msg], 400);
//            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function aprovar(Request $request)
    {
        $this->authorize('intermitente');
        $dados = $request->input();
        $dados['user_aprovacao_id'] = auth()->id();
        $dados['status'] = $dados['status_aprovacao'];
        $dados['data_aprovacao'] = (new DataHora())->dataHoraInsert();

        try {
            DB::beginTransaction();

            $intermitente = Intermitente::whereId($request->intermitente)->first();

            $intermitente->update([
                'user_aprovacao_id' => $dados['user_aprovacao_id'],
                'data_aprovacao' => $dados['data_aprovacao'],
                'obs_aprovacao' => $dados['obs_aprovacao'],
                'status' => $dados['status']
            ]);
            DB::commit();
            return response()->json([$intermitente], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error UPDATE INTERMITENTE:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }


    public function atualizar(Request $request)
    {
        $resultado = Intermitente::with('Tipo',
            'Cliente:id,nome,razao_social,cpf,cnpj,nome_fantasia',
            'Colaborador.Curriculo:id,nome,nascimento,rg,orgao_expeditor',
            'ResponsavelLancamento:id,nome',
            'ResponsavelAprovacao:id,nome'
        );

        if (auth()->user()->cliente_id != User::BPSE) {
            $resultado->whereClienteId(auth()->user()->cliente_id);
        }

        $resultado = $resultado->orderByDesc('created_at')->paginate($request->pages);

        $tipos = IntermitenteTipo::orderBy('label')->whereAtivo(true)->get();
        $areas = AreaEtiqueta::orderBy('label')->whereAtivo(true)->get();


        $data = new DataHora();
        $intervalo = $data->dataCompleta() . ' até ' . $data->addDia(7);

        $clientes = Cliente::whereAtivo(true)->get();

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'tipos' => $tipos,
                'cliente_id' => auth()->user()->cliente_id,
                'intervalo' => $intervalo,
                'areas' => $areas,
                'listaClientes' => $clientes,
                'hoje' => (new DataHora())->dataCompleta()
            ]
        ]);
    }

    //anexos-----------------------------------------------
    public function uploadAnexos(Request $request)
    {
        return Arquivo::uploadAnexos($request, Arquivo::MIMEAPENASIMAGENSPDF, Arquivo::DISCO_CIH);
    }

    public function anexoShow(Request $request, $arquivo)
    {
        return Arquivo::anexoShow(Arquivo::DISCO_CIH, $arquivo);
    }

    public function anexoDelete(Request $request, $arquivo)
    {
        return Arquivo::anexoDelete(Arquivo::DISCO_CIH, $arquivo);
    }

    //anexo ou foto
    public function download(Request $request, $arquivo)
    {
        return Arquivo::anexoDownload(Arquivo::DISCO_CIH, $arquivo);
    }
}
