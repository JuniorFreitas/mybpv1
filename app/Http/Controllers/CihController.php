<?php

namespace App\Http\Controllers;

use App\Jobs\Admissao\Apontamento\Cih\JobCihAprovarReprovar;
use App\Jobs\Admissao\Apontamento\Cih\JobCihStore;
use App\Jobs\JobExportaCihCsvFinal;
use App\Jobs\JobExportaPdf;
use App\Models\AreaEtiqueta;
use App\Models\Arquivo;
use App\Models\CentroCusto;
use App\Models\Cih;
use App\Models\CihTag;
use App\Models\Cliente;
use App\Models\ClienteConfig;
use App\Models\FeedbackCurriculo;
use App\Models\User;
use App\Services\Cih\CihAcessoService;
use App\Services\Cih\CihColaboradorPayloadMapper;
use App\Services\Cih\CihQueryBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use MasterTag\DataHora;

class CihController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
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
    public function store(Request $request): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
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
            $usuario = auth()->user();
            $modelo_cih_config = $usuario->EmpresaConfiguracoes->modelo_cih;


            DB::beginTransaction();
            $dados['tag_id'] = $dados['tag_id'] > 0 ? $dados['tag_id'] : null;
            $dados['area_id'] = $dados['area_id'] > 0 ? $dados['area_id'] : null;
            $dados['centro_custo_id'] = $dados['centro_custo_id'] > 0 ? $dados['centro_custo_id'] : null;
            $dados['empresa_id'] = auth()->user()->empresa_id;

            if ($modelo_cih_config == Cih::CONFIG_CENTRO_DE_CUSTO) {
                $centroDeCusto = CentroCusto::find($dados['centro_custo_id']);
                $dados['gestor_id'] = $centroDeCusto->Gestor ? $centroDeCusto->Gestor->id : null;
            }

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
                $jobDados = [
                    'empresa_id' => auth()->user()->empresa_id,
                    'empresa' => Cliente::whereId(auth()->user()->empresa_id)->select(['id', 'razao_social', 'apelido'])->first(),
                    'nome_de' => auth()->user()->nome,
                    'email_de' => auth()->user()->login,
                    'varios_colaboradores' => count($dados['colaboradores']) > 1 ? 'Sim' : 'Não',
                    'tipo' => $dados['tag_id'] != 0 ? CihTag::find($dados['tag_id'])->label : $dados['outra_tag'],
                    'data_lancamento' => (new DataHora($dados['data_lancamento']))->dataCompleta(),
                    'cih_id' => $cih->id
                ];

                if ($modelo_cih_config == Cih::CONFIG_CENTRO_DE_CUSTO) {
                    $gestor = $centroDeCusto->Gestor;
                    $jobDados['centro_de_custo'] = $centroDeCusto->label;
                } else {
                    $gestor = User::whereEmpresaId(auth()->user()->empresa_id)->whereId($dados['gestor_id'])->first();
                    $jobDados['area'] = $dados['area_id'] == 0 ? $dados['outra_area'] : AreaEtiqueta::find($dados['area_id'])->label;
                }

                $jobDados['nome_para'] = $gestor->nome;
                $jobDados['email_para'] = $gestor->login;


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

        $modelo_cih_config = auth()->user()->EmpresaConfiguracoes->modelo_cih;

        $cih->load([
            'Colaboradores' => function ($query) {
                $query->select(['feedback_curriculos.id', 'feedback_curriculos.curriculo_id']);
            },
            'Colaboradores.Curriculo:id,nome',
            'Colaboradores.Admissao:id,feedback_id,cargo,centro_custo_id',
            'Colaboradores.Admissao.CentroCusto:id,label',
            'Colaboradores.Demissao:id,feedback_id',
            'Anexos',
            'Tag:id,label,anexo_obrigatorio',
            'GestorAprovacao:id,nome',
            'ResponsavelLancamento:id,nome',
            'ResponsavelAprovacao:id,nome',
            'RhAprovacao:id,nome',
        ]);

        if ($modelo_cih_config == Cih::CONFIG_CENTRO_DE_CUSTO) {
            $cih->load('CentroDeCusto:id,label');
        } else {
            $cih->load('Area:id,label');
        }

        $cih->setAttribute(
            'colaboradores',
            (new CihColaboradorPayloadMapper())->mapForModal($cih->Colaboradores)
        );
        $cih->unsetRelation('Colaboradores');

        return $cih;
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
            if (is_null($dados['resposta_rh'])) {
                if (!app(CihAcessoService::class)->podeAprovarComoGestor(auth()->user(), $cih)) {
                    return response()->json([
                        'msg' => 'Você só pode aprovar CIH sob sua responsabilidade.',
                    ], 403);
                }

                $dadosValidados = \Validator::make($dados, [
                    'status' => 'required',
                ]);

                if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
                    return response()->json([
                        'msg' => 'Erro ao Salvar Informações',
                        'erros' => $dadosValidados->errors()
                    ], 400);
                }

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
                    'status' => ucfirst($cih->status),
                    'cih_id' => $cih->id
                ];
            } else {
                $dadosValidados = \Validator::make($dados, [
                    'resposta_rh' => 'required',
                ]);

                if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
                    return response()->json([
                        'msg' => 'Erro ao Salvar Informações',
                        'erros' => $dadosValidados->errors()
                    ], 400);
                }

                $cih->update([
                    'user_rh_id' => auth()->id(),
                    'data_aprovacao_rh' => (new DataHora())->dataHoraInsert(),
                    'obs_rh' => $dados['obs_rh'],
                    'resposta_rh' => $dados['resposta_rh']
                ]);
                DB::commit();

                $jobDados = [
                    'empresa_id' => auth()->user()->empresa_id,
                    'empresa' => Cliente::whereId(auth()->user()->empresa_id)->select(['id', 'razao_social', 'apelido'])->first(),
                    'nome_de' => $cih->RhAprovacao->nome,
                    'email_de' => $cih->RhAprovacao->login,
                    'nome_para' => $cih->ResponsavelLancamento->nome,
                    'email_para' => $cih->ResponsavelLancamento->login,
                    'tipo' => $cih->tag_id != 0 ? CihTag::find($cih->tag_id)->label : $cih->outra_tag,
                    'status' => ucfirst($cih->resposta_rh),
                    'cih_id' => $cih->id
                ];
            }

            $modelo_cih_config = auth()->user()->EmpresaConfiguracoes->modelo_cih;
            if ($modelo_cih_config == Cih::CONFIG_CENTRO_DE_CUSTO) {
                $jobDados['centro_de_custo'] = $cih->CentroDeCusto->label;
            } else {
                $jobDados['area'] = $cih->area_id == 0 ? $cih->outra_area : AreaEtiqueta::find($cih->area_id)->label;
            }

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
        $resultado = $this->filtro($request)->paginate($request->pages ?? 100);
        $usuario = auth()->user();

        $tags = CihTag::query()
            ->select(['id', 'label', 'anexo_obrigatorio'])
            ->orderBy('label')
            ->whereAtivo(true)
            ->get();

        $areas = AreaEtiqueta::query()
            ->select(['id', 'label'])
            ->orderBy('label')
            ->whereAtivo(true)
            ->get();

        $centros_de_custo = [];
        if ($usuario->EmpresaConfiguracoes->modelo_cih == ClienteConfig::CENTRO_DE_CUSTO) {
            $centros_de_custo = CentroCusto::query()
                ->select(['id', 'label', 'gestor_id'])
                ->with('Gestor:id,nome')
                ->orderBy('label')
                ->whereAtivo(true)
                ->get();
        }

        $gestores = Cih::query()
            ->select('gestor_id')
            ->with('GestorAprovacao:id,nome')
            ->whereNotNull('gestor_id')
            ->distinct()
            ->get();

        $data = new DataHora();
        $intervalo = $data->dataCompleta() . ' até ' . $data->addDia(7);

        $mapper = new CihColaboradorPayloadMapper();
        $items = collect($resultado->items())->transform(function ($item) use ($mapper) {
            $item->setAttribute('colaboradores', $mapper->mapForModal($item->Colaboradores));
            $item->unsetRelation('Colaboradores');
            return $item;
        });

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $items,
                'tags' => $tags,
                'intervalo' => $intervalo,
                'config_modelo_cih' => $usuario->EmpresaConfiguracoes->modelo_cih,
                'usuario_logado_id' => auth()->id(),
                'permissoes' => [
                    'admissao_cih_lancar' => auth()->user()->can('admissao_cih_lancar'),
                    'admissao_cih_aprovar' => auth()->user()->can('admissao_cih_aprovar'),
                    'admissao_cih_privilegio_adm' => auth()->user()->can('admissao_cih_privilegio_adm'),
                    'admissao_cih_ver_todas' => auth()->user()->can('admissao_cih_ver_todas'),
                    'aprovar_por_gestor' => auth()->user()->can('privilegio_aprovar_por_gestor'),
                    'aprovar_por_rh' => auth()->user()->can('privilegio_aprovar_por_rh')
                ],
                'areas' => $areas,
                'gestores' => $gestores,
                'centros_de_custo' => $centros_de_custo,
                'cc' => (new CentroCusto())->listaCentroCustoPorCnpj($usuario->empresa_id),
                'hoje' => (new DataHora())->dataCompleta()
            ]
        ]);
    }

    public function ativaDesativa(Request $request)
    {
        $cihTipo = CihTag::find($request->id);
        $cihTipo->ativo = !$cihTipo->ativo;
        $cihTipo->save();
        $cihTipo->refresh();
        return response()->json(['ativo' => $cihTipo->ativo], 201);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function filtro(Request $request)
    {
        return CihQueryBuilder::forListing(auth()->user(), $request->all());
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function export(Request $request)
    {
        $modelo_cih_config = auth()->user()->EmpresaConfiguracoes->modelo_cih;
        $nameArquivo = "admissao_cih" . rand(1000, 9999) . "_" . date('YmdHis') . ".csv";
        $filtros = $request->all();
        \Log::info('Filtros: ' . json_encode($filtros));
        JobExportaCihCsvFinal::dispatch(
            auth()->id(),
            "Admissão - CIH",
            $nameArquivo,
            $filtros,
            $modelo_cih_config
        );

        return response()->json(['msg' => 'Estamos gerando seu arquivo CSV, assim que finalizado você será notificado.']);
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
        $this->authorize('cadastro_tipos_cih');
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
     * @return \Illuminate\Http\JsonResponse
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

        foreach ($resultado as $key => $row) {
            foreach ($row->colaboradores as $colaborador) {
                $rows[$key] = [
                    'colaborador' => $colaborador->Curriculo->nome,
                    'cargo' => $colaborador->VagaAberta->Vaga->nome,
                    'data_ocorrencia' => $row->data_lancamento ?: '',
                    'tag' => $row->Tag ? $row->Tag->label : $row->outra_tag,
                    'responsavel_lancamento' => $row->ResponsavelLancamento ? $row->ResponsavelLancamento->nome : '',
                    'acao' => $row->acao,
                    'status' => $row->status ?: "aguardando",
                    'data_aprovacao' => $row->data_aprovacao ?: '',
                    'responsavel_aprovacao' => $row->ResponsavelAprovacao ? $row->ResponsavelAprovacao->nome : '',
                    'resposta_rh' => $row->resposta_rh ?: "",
                    'data_aprovacao_rh' => $row->data_aprovacao_rh ?: '',
                    'rh_aprovacao' => $row->RhAprovacao ? $row->RhAprovacao->nome : '',
                ];

                $modelo_cih_config = auth()->user()->EmpresaConfiguracoes->modelo_cih;
                if ($modelo_cih_config == Cih::CONFIG_CENTRO_DE_CUSTO) {
                    $rows[$key]['centro_de_custo'] = $row->CentroDeCusto ? $row->CentroDeCusto->label : '';
                } else {
                    $rows[$key]['area'] = $row->area_id && $row->Area ? $row->Area->label : $row->outra_area;
                }
            }
        }

        $dados = [
            'data_inicio' => $dataInicio,
            'data_fim' => $dataFim,
            'filtro_periodo' => $filtroPeriodo,
            'modelo_cih_config' => $modelo_cih_config,
            'rows' => $rows];


        $view = 'pdf.admissao.apontamento.cih';
        $nameArquivo = "relatorio_cih_" . (new DataHora())->nomeUnico() . ".pdf";

        $usuario['empresa_id'] = auth()->user()->empresa_id;
        $usuario['id'] = auth()->user()->id;
        $usuario['nome'] = auth()->user()->nome;
        $usuario['logo'] = null;
        $usuario['razao_social'] = auth()->user()->DadosEmpresa->razao_social;
        $usuario['endereco'] = auth()->user()->Empresa->endereco_completo;
        $usuario['cnpj'] = auth()->user()->DadosEmpresa->cnpj;
        if (count(auth()->user()->ClientesLogo) > 0) {
            $usuario['logo'] = auth()->user()->ClientesLogo[0]->urlThumb;
        }

        JobExportaPdf::dispatch($usuario, "Relatório - CIH (PDF)", $dados, $nameArquivo, $view);
        return response()->json(['msg' => 'Estamos gerando seu arquivo pdf, assim que finalizado você será notificado.']);
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
