<?php

namespace App\Http\Controllers;

use App\Classes\ZapNotificacao;
use App\Jobs\JobExportaExcel;
use App\Models\AreaEtiqueta;
use App\Models\Arquivo;
use App\Models\CentroCusto;
use App\Models\Cliente;
use App\Models\ClienteConfig;
use App\Models\Intermitente;
use App\Models\IntermitenteProrrogacao;
use App\Models\IntermitenteTipo;
use App\Models\TelefoneCurriculo;
use App\Models\User;
use DB;
use Illuminate\Support\Str;
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
        $this->authorize('admissao_intermitente');
        $dados = $request->input();
        $dados['user_lancamento_id'] = auth()->id();
        $periodo = $dados['data_lancamento'];
        $area = AreaEtiqueta::find($dados['area_id']);
        $centroDeCusto = CentroCusto::find($dados['centro_custo_id']);
        $dados['range_convocacao'] = explode(' até ', $dados['data_lancamento']);
        $dados['data_lancamento'] = (new DataHora($dados['range_convocacao'][0] . ' ' . date('H:m:s')))->dataHoraInsert(); // data concocação
        $dados['encerramento_previsto'] = (new DataHora($dados['range_convocacao'][1]))->dataInsert();; // data fim convocacao
        $dados['varios_colaboradores'] = count($dados['colaboradores']) > 1;


        $dadosValidados = \Validator::make($dados, [
            'tipo_id' => 'required',
            'colaboradores' => 'required|array|min:1',
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar Informações',
                'erros' => $dadosValidados->errors()
            ], 400);
        } else {
            try {
                DB::beginTransaction();
                $permite_envio_whatsapp = ClienteConfig::whereClienteId(auth()->user()->empresa_id)->first();
                $permite_envio_whatsapp = !empty($permite_envio_whatsapp) && $permite_envio_whatsapp->envia_whatsapp;
                $empresa = Cliente::find(auth()->user()->empresa_id);

                $dados['tipo_id'] = $dados['tipo_id'] > 0 ? $dados['tipo_id'] : null;
                $dados['area_id'] = $dados['area_id'] > 0 ? $dados['area_id'] : null;

                foreach ($dados['colaboradores'] as $colaborador) {
                    $dados['feedback_id'] = $colaborador['id'];
                    $dados['hash_colaborador'] = Str::uuid();
                    $intermitente = Intermitente::create($dados);

                    $curriculo = $colaborador['curriculo'];

                    $colaborador['envia_whatsapp'] = true;

                    $resposta_sim = route('respostaConvocacao', ['s', $dados['hash_colaborador']]);
                    $resposta_nao = route('respostaConvocacao', ['n', $dados['hash_colaborador']]);

//                    $resposta_sim = str_replace('http://localhost:8000', 'https://mybp.com.br', $resposta_sim);
//                    $resposta_nao = str_replace('http://localhost:8000', 'https://mybp.com.br', $resposta_nao);

                    $dadosEnvio = [
                        'colaborador' => $curriculo['nome'],
                        'email' => $curriculo['email'],
                        'periodo' => $periodo,
                        'centro_de_custo' => $centroDeCusto->label,
                        'area' => $area->label,
                        'resposta_sim' => $resposta_sim,
                        'resposta_nao' => $resposta_nao,
                        'prazo_resposta_expiracao' => (new DataHora($intermitente->prazo_resposta_expiracao))->dataHoraCompleta(),
                        'empresa_id' => $empresa->id,
                        'empresa' => $empresa->razao_social,
                    ];

                    if ($permite_envio_whatsapp) {
                        $mensagem = "Prezado(a), *{$dadosEnvio['colaborador']}*";
                        $mensagem .= "\nConforme seu modelo de contrato INTERMITENTE prevê a convocação ao trabalho, ";
                        $mensagem .= "viemos através dessa mensagem informá-lo(a) que o(a) Sr(a). está convocado(a) para trabalho ";
                        $mensagem .= "no período de *".$dadosEnvio['periodo']."* no *".$dadosEnvio['centro_de_custo']." / ".$dadosEnvio['area']."*.";
                        $mensagem .= "\n\nPara isso, gentileza confirmar aceite de convocação, conforme links abaixo ⬇️";
                        $mensagem .= "\n\nPara *aceitar*, clique no link a seguir:\n".$dadosEnvio['resposta_sim'];
                        $mensagem .= "\n\nPara *recusar*, clique no link a seguir:\n".$dadosEnvio['resposta_nao'];
                        $mensagem .= "\n\nInformamos que você tem até *".$dadosEnvio['prazo_resposta_expiracao']."* para sinalizar a sua resposta.";
                        $mensagem .= "\n\nUm forte abraço da equipe *" . $dadosEnvio['empresa'] . "*\n\n_Esta mensagem foi enviada automaticamente pela plataforma *MyBP*, por favor não responda._";

                        $telefonePrincipal = TelefoneCurriculo::whereCurriculoId($curriculo['id'])->wherePrincipal(true)->first();

//                        if ($telefonePrincipal->tipo == 'whatsapp') {
                            (new ZapNotificacao())->enviar([
                                'enviado_id' => $curriculo['id'],
                                'telefone' => $telefonePrincipal->sonumero,
                                'mensagem' => $mensagem,
                            ]);
//                        }
                    }
                }

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
                // WHATSAPP
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
        $this->authorize('admissao_intermitente');
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
        $this->authorize('admissao_intermitente');
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
        $this->authorize('admissao_intermitente');
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
        $this->authorize('admissao_intermitente');
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
        $resultado = $this->filtro($request)->paginate($request->pages);

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
                'empresa_id' => auth()->user()->empresa_id,
                'intervalo' => $intervalo,
                'areas' => $areas,
                'listaClientes' => $clientes,
                'hoje' => (new DataHora())->dataCompleta()
            ]
        ]);
    }

    public function filtro(Request $request)
    {
        $resultado = Intermitente::with('Tipo',
            'Cliente:id,nome,razao_social,cpf,cnpj,nome_fantasia',
            'Colaborador.Curriculo:id,nome,nascimento,rg,orgao_expeditor',
            'ResponsavelLancamento:id,nome',
            'ResponsavelAprovacao:id,nome'
        );

//        if (auth()->user()->cliente_id != User::BPSE) {
//            $resultado->whereClienteId(auth()->user()->cliente_id);
//        }

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Colaborador.Curriculo', function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->campoBusca . '%');
            });
        }

        return $resultado->orderByDesc('created_at');

    }

    public function export(Request $request)
    {
        $resultado = $this->filtro($request)->get();

        $head = [
            'Nome',
            'Responsavel lançamento',
            'Data lançamento',
            'Responsavel aprovacao',
            'Data aprovação',
            'Status',
            'Tipo',
            'Ação',
            'Área',
            'Devolve API',
            'Devolve grachá'
        ];

        $rows = [];

        foreach ($resultado as $row) {
            $rows[] = [
                $row->Colaborador->Curriculo->nome,
                $row->ResponsavelLancamento->nome,
                (new DataHora($row->data_lancamento))->dataCompleta() . ' ' . substr((new DataHora($row->data_lancamento))->horaCompleta(), 0, 5),
                $row->ResponsavelAprovacao ? $row->ResponsavelAprovacao->nome : "",
                (new DataHora($row->data_aprovacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_aprovacao))->horaCompleta(), 0, 5),
                $row->status,
                $row->Tipo->label,
                $row->acao,
                $row->area_id,
                $row->devolve_epi ? $row->devolve_epi = true ? "SIM":"NÂO" : "",
                $row->devolve_cracha ? $row->devolve_cracha = true ? "SIM":"NÂO" : ""
            ];
        }

        $nameArquivo = "intermitente" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Intermitente", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
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

    public function respostaConvocacao($resposta, $hash){
        $convocacao = Intermitente::withoutGlobalScopes()->whereHashColaborador($hash)->whereNull('resposta_colaborador')->first();

        if($convocacao){
            $convocacao->update([
                'resposta_colaborador' => $resposta == 's' ? 'Sim' : 'Não',
                'data_resposta_colaborador' => (new DataHora())->dataHoraInsert(),
            ]);

            return response()->json([
                'sucesso' => true,
                'mensagem' => 'Obrigado por sua resposta',
            ], 200);

        }else{
            return response()->json([
                'erro' => true,
                'mensagem' => 'Link expirado',
            ], 400);
        }
    }
}
