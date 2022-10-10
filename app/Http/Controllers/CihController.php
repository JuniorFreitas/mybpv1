<?php

namespace App\Http\Controllers;

use App\Exports\admissao\apontamento\cihExport;
use App\Jobs\Admissao\Apontamento\Cih\JobCihAprovarReprovar;
use App\Jobs\Admissao\Apontamento\Cih\JobCihStore;
use App\Jobs\JobExportaExcel;
use App\Models\AreaEtiqueta;
use App\Models\Arquivo;
use App\Models\Cih;
use App\Models\CihTag;
use App\Models\Cliente;
use App\Models\FeedbackCurriculo;
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
        $dados['varios_colaboradores'] = count($dados['colaboradores']) > 1;

        $dadosValidados = \Validator::make($dados, [
            'tag_id' => 'required',
            'outra_tag' => [
                function ($attribute, $value, $fail) use ($dados) {
                    if ($dados['tag_id'] == 0 && $value == '') {
                        $fail('O campo especifique deve ser preenchido.');
                    }
                }],
            'colaboradores' => 'required|array|min:1',
//            'feedback_id' => 'required_if:varios_colaboradores,0',
//            'colaboradores_avulso' => [
//                function ($attribute, $value, $fail) use ($dados) {
//                    if ($dados['varios_colaboradores'] == 1 && $value == '') {
//                        $fail('Preencha o campo informando os colaboradores.');
//                    }
//                }],
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

            $cih = Cih::create($dados);

            foreach ($dados['colaboradores'] as $colaborador) {
                $cih->Colaboradores()->attach($colaborador['id']);
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
                        $cih->Anexos()->attach($arquivo->id);
                    }
                }
            }
            if ($dados['gestor_id']) {
                $gestor = User::whereEmpresaId(auth()->user()->empresa_id)->whereId($dados['gestor_id'])->first();

                $jobDados = [
                    'empresa_id' => auth()->user()->empresa_id,
                    'empresa' => Cliente::whereId(auth()->user()->empresa_id)->select(['id', 'razao_social', 'apelido'])->first(),
                    'nome_de' => auth()->user()->nome,
                    'email_de' => auth()->user()->login,
                    'nome_para' => $gestor->nome,
                    'email_para' => $gestor->login,
                    'varios_colaboradores' => count($dados['colaboradores']) > 1 ? 'Sim' : 'Não',
                    'tipo' => $dados['tag_id'] != 0 ? CihTag::find($dados['tag_id'])->label : $dados['outra_tag'],
                    'area' => $dados['area_id'] == 0 ? $dados['outra_area'] : AreaEtiqueta::find($dados['area_id'])->label,
                    'data_lancamento' => (new DataHora($dados['data_lancamento']))->dataCompleta(),
                    'cih_id' => $cih->id
                ];

                JobCihStore::dispatch($jobDados);
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
     * @param Cih $cih
     * @return Cih
     */
    public function edit(Cih $cih)
    {

        $cih->tag_id = is_null($cih->tag_id) ? 0 : $cih->tag_id;
        $cih->area_id = is_null($cih->area_id) ? 0 : $cih->area_id;
        $cih->status_aprovacao = $cih->status;

        $cih->autocomplete_label_gestor_modal = $cih->GestorAprovacao ? $cih->GestorAprovacao->nome : '';
        $cih->autocomplete_label_gestor_modal_anterior = $cih->GestorAprovacao ? $cih->GestorAprovacao->nome : '';

        return $cih->load('Anexos', 'Tag', 'Area', 'Colaboradores', 'ResponsavelLancamento:id,nome', 'ResponsavelAprovacao:id,nome');
    }

    /**
     * @param Request $request
     * @param Cih $cih
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function aprovar(Request $request, Cih $cih)
    {
        $this->authorize('admissao_cih_aprovar');
        $dados = $request->input();
        $dados['user_aprovacao_id'] = auth()->id();
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

            $jobDados = [
                'empresa_id' => auth()->user()->empresa_id,
                'empresa' => Cliente::whereId(auth()->user()->empresa_id)->select(['id', 'razao_social', 'apelido'])->first(),
                'nome_de' => $cih->ResponsavelAprovacao->nome,
                'email_de' => $cih->ResponsavelAprovacao->login,
                'nome_para' => $cih->ResponsavelLancamento->nome,
                'email_para' => $cih->ResponsavelLancamento->login,
                'tipo' => $cih->tag_id != 0 ? CihTag::find($cih->tag_id)->label : $cih->outra_tag,
                'area' => $cih->area_id == 0 ? $cih->outra_area : AreaEtiqueta::find($cih->area_id)->label,
                'status' => ucfirst($cih->status),
                'cih_id' => $cih->id
            ];

            JobCihAprovarReprovar::dispatch($jobDados);

            return response()->json([$cih], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error UPDATE CIH:  {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . User::find(auth()->id())->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    /**
     * @param $feedback
     * @return \Illuminate\Http\JsonResponse
     */
    public function atualizarHistorico($feedback)
    {
        $feedback_id = \Crypt::decrypt($feedback);
        $resultado = FeedbackCurriculo::select(['id', 'vagas_abertas_id'])->find($feedback_id)->load([
            'Cih.Tag',
            'Cih.Area',
            'Cih.Colaboradores.Curriculo:id,nome,nascimento,rg,orgao_expeditor',
            'Cih.ResponsavelLancamento:id,nome',
            'Cih.ResponsavelAprovacao:id,nome'
        ]);

        return response()->json($resultado);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->pages);

        $periodo = Cih::get();
        $tags = CihTag::orderBy('label')->whereAtivo(true)->get();
        $areas = AreaEtiqueta::orderBy('label')->whereAtivo(true)->get();
        $data = new DataHora();
        $intervalo = $data->dataCompleta() . ' até ' . $data->addDia(7);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'tags' => $tags,
                'periodo' => $periodo,
                'intervalo' => $intervalo,
                'permissoes' => [
                    'admissao_cih_lancar' => auth()->user()->can('admissao_cih_lancar'),
                    'admissao_cih_aprovar' => auth()->user()->can('admissao_cih_aprovar'),
                ],
                'areas' => $areas,
                'hoje' => (new DataHora())->dataCompleta()
            ]
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filtro(Request $request)
    {
        $resultado = Cih::with('Tag:id,label', 'Area',
            'Colaboradores',
            'ResponsavelLancamento:id,nome',
            'ResponsavelAprovacao:id,nome'
        );

        $filtroPeriodo = $request->filtroPeriodo == 'true';
        if ($filtroPeriodo) {
            $periodo = explode(' até ', $request->periodo);
            $dataInicio = new DataHora($periodo[0], ' 00:00:00');
            $dataFim = new DataHora($periodo[1], ' 23:59:59');
            $resultado->where('data_lancamento', '>=', $dataInicio->dataInsert())->where('data_lancamento', '<=', $dataFim->dataInsert());
        }

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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request)
    {
        $resultado = $this->filtro($request)->get();
        $head = [
            "Colaborador",
            "Cargo",
            "Área",
            "Data Ocorrência",
            "Ocorrência",
            "Responsável Lançamento",
            "Ação",
            "Status",
            "Data Status",
            "Responsável Status"
        ];

        $rows = [];

        foreach ($resultado as $row) {
            foreach ($row->colaboradores as $colaborador) {
                $rows[] = [
                    'colaborador' => $colaborador->Curriculo->nome,
                    'cargo' => $colaborador->VagaAberta->Vaga->nome,
                    'area' => $row->area_id ? $row->Area->label : $row->outra_area,
                    'data_ocorrencia' => $row->data_lancamento ?: '',
                    'tag' => $row->Tag ? $row->Tag->label : $row->outra_tag,
                    'responsavel_lancamento' => $row->ResponsavelLancamento ? $row->ResponsavelLancamento->nome : '',
                    'acao' => $row->acao,
                    'status' => $row->status ?: "aguardando",
                    'data_aprovacao' => $row->data_aprovacao ?: '',
                    'responsavel_aprovacao' => $row->ResponsavelAprovacao ? $row->ResponsavelAprovacao->nome : '',
                ];
            }
        }


        $nameArquivo = "admissao_cih" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Admissão - CIH", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function tipoCihIndex(Request $request)
    {
        return view('g.cadastros.tipocih.index');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
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

    /**
     * @param CihTag $tipocih
     * @return CihTag
     */
    public function tipoCihEdit(CihTag $tipocih)
    {
        return $tipocih;
    }

    /**
     * @param Request $request
     * @param CihTag $tipocih
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function relatorioPdf(Request $request)
    {
        $filtroPeriodo = $request->filtroPeriodo == 'true';
        $dataInicio = "";
        $dataFim = "";
        if ($filtroPeriodo) {
            $intervalo = explode(' até ', $request->periodo);
            $dataInicio = (new DataHora($intervalo[0] . ' 00:00:00'))->dataHoraInsert();
            $dataFim = (new DataHora($intervalo[1] . ' 23:59:59'))->dataHoraInsert();
        }

        $resultado = $this->filtro($request)->orderBy('data_aprovacao')->get();

        $rows = [];

        foreach ($resultado as $row) {
            foreach ($row->colaboradores as $colaborador) {
                $rows[] = [
                    'colaborador' => $colaborador->Curriculo->nome,
                    'cargo' => $colaborador->VagaAberta->Vaga->nome,
                    'area' => $row->area_id ? $row->Area->label : $row->outra_area,
                    'data_ocorrencia' => $row->data_lancamento ?: '',
                    'tag' => $row->Tag ? $row->Tag->label : $row->outra_tag,
                    'responsavel_lancamento' => $row->ResponsavelLancamento ? $row->ResponsavelLancamento->nome : '',
                    'acao' => $row->acao,
                    'status' => $row->status ?: "aguardando",
                    'data_aprovacao' => $row->data_aprovacao ?: '',
                    'responsavel_aprovacao' => $row->ResponsavelAprovacao ? $row->ResponsavelAprovacao->nome : '',
                ];
            }
        }

        $empresa = User::whereId(auth()->user()->empresa_id)->first();
        $pdf = PDF::loadView('pdf.admissao.apontamento.cih', compact('rows', 'empresa', 'dataInicio', 'dataFim', 'filtroPeriodo'));
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
