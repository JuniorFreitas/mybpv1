<?php

namespace App\Http\Controllers;

use App\Exports\admissao\apontamento\cihExport;
use App\Jobs\JobExportaExcel;
use App\Models\AreaEtiqueta;
use App\Models\Arquivo;
use App\Models\Cih;
use App\Models\CihTag;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use MasterTag\DataHora;
use PDF;

class CihController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.admissao.apontamento.cih.index');
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
        $this->authorize('admissao_cih_lancar');
        $dados = $request->input();
        $dados['user_lancamento_id'] = auth()->id();
        $dados['data_lancamento'] = (new DataHora($dados['data_lancamento'] . ' ' . date('H:m:s')))->dataHoraInsert();
        $dados['outra_tag'] = $dados['tag_id'] == 0 ? $dados['outra_tag'] : 0;
        $dados['outra_area'] = $dados['area_id'] == 0 ? $dados['outra_area'] : null;

        $dadosValidados = \Validator::make($dados, [
            'tag_id' => 'required',
            'outra_tag' => [
                function ($attribute, $value, $fail) use ($dados) {
                    if ($dados['tag_id'] == 0 && $value == '') {
                        $fail('O campo especifique deve ser preenchido.');
                    }
                }],
            'feedback_id' => 'required_if:varios_colaboradores,0',
            'colaboradores_avulso' => [
                function ($attribute, $value, $fail) use ($dados) {
                    if ($dados['varios_colaboradores'] == 1 && $value == '') {
                        $fail('Preencha o campo informando os colaboradores.');
                    }
                }],
            'acao' => 'required',
            'anexos' => [function ($attribute, $value, $fail) use ($dados) {
                $CihTag = CihTag::where('id', $dados['tag_id'])->first();
                if ($CihTag && $CihTag->anexos_obrigatorios && count($value) == 0) {
                    $fail('É necessário anexar o(s) arquivo(s) obrigatório(s) para a tipo selecionado.');
                }
            }]
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao Salvar Informações',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();
            $dados['tag_id'] = $dados['tag_id'] > 0 ? $dados['tag_id'] : null;
            $dados['area_id'] = $dados['area_id'] > 0 ? $dados['area_id'] : null;
            $dados['empresa_id'] = auth()->user()->empresa_id;
            if ($dados['varios_colaboradores']) {
                unset($dados['feedback_id']);
            }

            $cih = Cih::create($dados);

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
                        $cih->Anexos()->attach($arquivo->id);
                    }
                }
            }

            DB::commit();
            return response()->json([$cih->load('Anexos')], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE CIH:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
//                return response()->json(['msg' => $msg], 400);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Cih $cih
     * @return \Illuminate\Http\Response
     */
    public function edit(Cih $cih)
    {
        if ($cih->feedback_id) {
            $cih->autocomplete_label_colaborador = "{$cih->Colaborador->Curriculo->nome} - {$cih->Colaborador->VagaAberta->VagaSelecionada->nome} - {$cih->Colaborador->VagaAberta->Municipio->uf}";
            $cih->autocomplete_label_colaborador_anterior = $cih->autocomplete_label_colaborador;
        }

        $cih->tag_id = is_null($cih->tag_id) ? 0 : $cih->tag_id;
        $cih->area_id = is_null($cih->area_id) ? 0 : $cih->area_id;
        $cih->status_aprovacao = $cih->status;


        return $cih->load('Anexos', 'Tag', 'Area');
    }

    /**
     * Aprovar the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Cih $cih
     * @return \Illuminate\Http\Response
     */
    public function aprovar(Request $request, Cih $cih)
    {
        $this->authorize('admissao_cih_aprovar');
        $dados = $request->input();
        $dados['user_aprovacao_id'] = auth()->id();
        $dados['status'] = $dados['status'];
        $dados['data_aprovacao'] = (new DataHora())->dataHoraInsert();

        try {
            DB::beginTransaction();
            $cih->update([
                'user_aprovacao_id' => $dados['user_aprovacao_id'],
                'data_aprovacao' => $dados['data_aprovacao'],
                'obs_aprovacao' => $dados['obs_aprovacao'],
                'status' => $dados['status']
            ]);
            DB::commit();
            return response()->json([$cih], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error UPDATE CIH:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function atualizarHistorico($feedback)
    {

        $resultado = Cih::whereFeedbackId($feedback)->with('Tag', 'Area',
            'Colaborador.Curriculo:id,nome,nascimento,rg,orgao_expeditor',
            'ResponsavelLancamento:id,nome',
            'ResponsavelAprovacao:id,nome'
        );

        $data = new DataHora();
        $intervalo = $data->dataCompleta() . ' até ' . $data->addDia(7);

        return response()->json([
            'itens' => $resultado->get(),
            'cliente_id' => auth()->user()->cliente_id,
            'intervalo' => $intervalo,
            //'listaClientes' => $clientes,
            'hoje' => (new DataHora())->dataCompleta()
        ]);
    }

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->pages);

        $periodo = Cih::all();
        $tags = CihTag::orderBy('label')->whereAtivo(true)->get();
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
                'tags' => $tags,
                'periodo' => $periodo,
                'cliente_id' => auth()->user()->cliente_id,
                'intervalo' => $intervalo,
                'areas' => $areas,
                'listaClientes' => $clientes,
                'hoje' => (new DataHora())->dataCompleta()
            ]
        ]);
    }

    public function filtro(Request $request)
    {
        $resultado = Cih::with('Tag:id,label', 'Area',
            'Colaborador.Curriculo:id,nome,nascimento,rg,orgao_expeditor',
            'ResponsavelLancamento:id,nome',
            'ResponsavelAprovacao:id,nome'
        );

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Colaborador.Curriculo', function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->campoBusca . '%');
            });
        }

        if ($request->filled('campoStatus')) {
            $resultado->whereStatus($request->campoStatus);
        }

        if ($request->filled('campoTags')) {
            $resultado->whereHas('Tag', function ($q) use ($request) {
                $q->whereId($request->campoTags);
            });
        }

        if ($request->filled('campoAreas')) {
            $resultado->whereHas('Area', function ($q) use ($request) {
                $q->whereId($request->campoAreas);
            });
        }

        return $resultado->orderByDesc('created_at');

    }

    public function export(Request $request)
    {
        $resultado = $this->filtro($request)->get();
        $head = [
            "Colaborador",
            "Área",
            "Data Ocorrência",
            "Ocorrência",
            "Responsável Lançamento",
            "Ação",
            "Obs Lançamento",
            "Status",
            "Data Status",
            "Responsável Status"
        ];

        $rows = [];

        foreach ($resultado as $row) {
            if ($row->varios_colaboradores) {
                $colaboradoresAvulsos = explode("\n", $row->colaboradores_avulso);
                foreach ($colaboradoresAvulsos as $colaborador) {
                    $rows[] = [
                        $colaborador,
                        $row->area_id ? $row->Area->label : $row->outra_area,
                        $row->data_lancamento ?: '',
                        $row->Tag->label,
                        $row->ResponsavelLancamento ? $row->ResponsavelLancamento->nome : '',
                        $row->acao,
                        $row->obs_lancamento ?: '',
                        $row->status ?: "aguardando",
                        $row->data_aprovacao ?: '',
                        $row->ResponsavelAprovacao ? $row->ResponsavelAprovacao->nome : '',
                    ];
                }
            } else {
                $rows[] = [
                    $row->Colaborador->Curriculo->nome,
                    $row->area_id ? $row->Area->label : $row->outra_area,
                    $row->data_lancamento ?: '',
                    $row->Tag->label,
                    $row->ResponsavelLancamento ? $row->ResponsavelLancamento->nome : '',
                    $row->acao,
                    $row->obs_lancamento ?: '',
                    $row->status ?: "aguardando",
                    $row->data_aprovacao ?: '',
                    $row->ResponsavelAprovacao ? $row->ResponsavelAprovacao->nome : '',
                ];
            }

        }

        $nameArquivo = "admissao_cih" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Admissão - CIH", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }

    public function tipoCihIndex(Request $request)
    {
        return view('g.cadastros.tipocih.index');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function tipoCihStore(Request $request)
    {
        $dados = $request->input();

        $regra = Rule::unique('cih_tags')->where(function ($query) use ($dados) {
            return $query->whereEmpresaId(auth()->user()->empresa_id)
                ->whereLabel($dados['label']);
        });

        $dadosValidados = \Validator::make($dados, [
            'label' => ['required', $regra]
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao criar novo tipo cih',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            CihTag::create($dados);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE TIPO CIH:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function tipoCihEdit(CihTag $tipocih)
    {
        return $tipocih;
    }

    public function tipoCihUpdate(Request $request, CihTag $tipocih)
    {
        $dados = $request->input();

        $regra = Rule::unique('cih_tags')->where(function ($query) use ($dados) {
            return $query->whereEmpresaId(auth()->user()->empresa_id)
                ->whereLabel($dados['label']);
        })->ignore($tipocih->id);

        $dadosValidados = \Validator::make($dados, [
            'label' => ['required', $regra]
        ]);

        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao atualizar novo tipo cih',
                'erros' => $dadosValidados->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $tipocih->update($dados);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error STORE TIPO CIH:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function tipoCihAtualizar(Request $request)
    {
        $this->authorize('cadastro_centrocusto');
        $porPagina = $request->get('porPagina');
        $resultado = CihTag::orderBy('id');

        if ($request->filled('campoBusca')) {
            $resultado->where('label', 'like', '%' . $request->campoBusca . '%');
        }

        $resultado = $resultado->paginate($porPagina);
        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'items' => $resultado->items(),
            ]
        ], 200);
    }

    public function relatorioPdf(Request $request)
    {
        $intervalo = explode(' até ', $request->intervalo);
        $dataInicio = (new DataHora($intervalo[0] . ' 00:00:00'))->dataHoraInsert();
        $dataFim = (new DataHora($intervalo[1] . ' 23:59:59'))->dataHoraInsert();


        $dados = Cih::with('Tag', 'Empresa',
            'Colaborador.Curriculo:id,nome,nascimento,rg,orgao_expeditor',
            'ResponsavelLancamento:id,nome',
            'ResponsavelAprovacao:id,nome'
        )->where('data_aprovacao', '>=', $dataInicio)
            ->where('data_aprovacao', '<=', $dataFim)
            ->whereStatus('aprovado');

        $resultado = $dados->orderBy('data_aprovacao')->get();

        $rows = [];

        foreach ($resultado as $row) {
            if ($row->varios_colaboradores) {
                $colaboradoresAvulsos = explode("\n", $row->colaboradores_avulso);
                foreach ($colaboradoresAvulsos as $colaborador) {
                    $rows[] = [
                        'colaborador' => $colaborador,
                        'area' => $row->area_id ? $row->Area->label : $row->outra_area,
                        'data_ocorrencia' => $row->data_lancamento ?: '',
                        'tag' => $row->Tag->label,
                        'responsavel_lancamento' => $row->ResponsavelLancamento ? $row->ResponsavelLancamento->nome : '',
                        'acao' => $row->acao,
                        'status'=>$row->status ?: "aguardando",
                        'data_aprovacao' => $row->data_aprovacao ?: '',
                        'responsavel_aprovacao' => $row->ResponsavelAprovacao ? $row->ResponsavelAprovacao->nome : '',
                    ];
                }
            } else {
                $rows[] = [
                    'colaborador' => $row->Colaborador->Curriculo->nome,
                    'area' => $row->area_id ? $row->Area->label : $row->outra_area,
                    'data_ocorrencia' => $row->data_lancamento ?: '',
                    'tag' => $row->Tag->label,
                    'responsavel_lancamento' => $row->ResponsavelLancamento ? $row->ResponsavelLancamento->nome : '',
                    'acao' => $row->acao,
                    'status'=>$row->status ?: "aguardando",
                    'data_aprovacao' => $row->data_aprovacao ?: '',
                    'responsavel_aprovacao' => $row->ResponsavelAprovacao ? $row->ResponsavelAprovacao->nome : '',
                ];
            }

        }


        $empresa = User::whereId(auth()->user()->empresa_id)->first();
        $pdf = PDF::loadView('pdf.admissao.apontamento.cih', compact('rows', 'empresa', 'dataInicio', 'dataFim'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream("relatorio_cih_" . (new DataHora())->nomeUnico() . ".pdf");
    }

    // Anexos-------------------------------------------------
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
