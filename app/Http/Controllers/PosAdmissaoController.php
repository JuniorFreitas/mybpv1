<?php

namespace App\Http\Controllers;

use App\Models\Admissao;
use App\Models\AuditoriaInterna;
use App\Models\AvaliacaoNoventaVencimento;
use App\Models\CentroCusto;
use App\Models\ClassificacaoRescisao;
use App\Models\Demissao;
use App\Models\EntrevistaDesligamento;
use App\Models\FeedbackCurriculo;
use App\Models\Formulario;
use App\Models\MotivoRescisao;
use App\Models\TipoAviso;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use MasterTag\DataHora;
use PDF;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PosAdmissaoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.posadmissao.index');
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $dados = $request->input();

        $dadosValidados = \Validator::make($dados, [
            'cipa' => 'required',
            'municipio_id' => 'required'
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao cadastrar Vaga',
                'erros' => $dadosValidados->errors()
            ], 400);

        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function demitir(Request $request)
    {
        $dados = $request->input();
        $dadosDemissao = $dados['demissao'];

        $dadosValidados = \Validator::make($dadosDemissao, [
            'cipa' => 'required',
            'motivo_rescisao_id' => 'required',
            'tipo_aviso_id' => 'required',
            'solicitado_por' => 'required',
        ]);
        if ($dadosValidados->fails()) { // se o array de erros contem 1 ou mais erros..
            return response()->json([
                'msg' => 'Erro ao demitir',
                'erros' => $dadosValidados->errors()
            ], 400);
        }
        try {
            DB::beginTransaction();
            $feedback = FeedbackCurriculo::find($dados['feedback_id']);
            $feedback->Admissao()->update([
                'status' => Admissao::STATUS_DEMITIDO
            ]);
            $feedback->Demissao()->create($dadosDemissao);
            User::find($feedback->curriculo_id)->update([
                'ativo' => false
            ]);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'msg' => 'Erro ao demitir',
                'erros' => $e->getTraceAsString()
            ], 400);
        }
    }

    public function demissaoPdf($id)
    {

        $dados = Demissao::whereId($id)->with('motivoRescisao', 'tipoAviso')->first();

        if ($dados->Feedback->AvaliacaoNoventaVencimento !== null) {
            $datasVencimento = AvaliacaoNoventaVencimento::find($dados->Feedback->AvaliacaoNoventaVencimento->id);
            if ($datasVencimento !== null) {
                $data = new DataHora();
                $dataInicial = $data->diferencaDias($dados->data_desmobilizacao, $datasVencimento->prazo_dia_inicial);
                $dataFinal = $data->diferencaDias($dados->data_desmobilizacao, $datasVencimento->prazo_dia_final);

                if ($dataInicial > 0) {
                    $dados['termino_previsto'] = $datasVencimento->prazo_dia_inicial;
                }
                if ($dataFinal > 0 && $dataInicial < 0) {
                    $dados['termino_previsto'] = $datasVencimento->prazo_dia_final;
                }
                if ($dataFinal < 0 && $dataInicial < 0) {
                    $dados['termino_previsto'] = '';
                }
            } else {
                $dados['termino_previsto'] = '';
            }
        }


        $pdf = PDF::loadView('pdf.admissao.posadmissao.' . $dados->motivoRescisao->nome_pdf, compact('dados'));
        $pdf->setPaper('A4', 'portrait');
        return $pdf->stream($dados->motivoRescisao->nome_pdf . (new DataHora())->nomeUnico() . ".pdf");
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Admissao $admissao
     * @return \Illuminate\Http\Response
     */
    public function show(Admissao $admissao)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Admissao $admissao
     * @return Admissao|\Illuminate\Http\Response
     */
    public function edit($id)
    {
        $admissao = Admissao::whereFeedbackId($id)->first();

        $admissao->load('Feedback.Curriculo', 'Demissao', 'Feedback.Empresa', 'Feedback.VagaSelecionada', 'Feedback.MotivoRescisao', 'Feedback.TipoAviso', 'Feedback.ClassificacaoRescisao', 'Feedback.EntrevistaDesligamento');
        $admissao->motivo = !is_null($admissao->Feedback->MotivoRescisao) ? $admissao->Feedback->MotivoRescisao->motivo_id : "";
        $admissao->outromotivo = null;
        if (!is_null($admissao->Feedback->MotivoRescisao) && $admissao->Feedback->MotivoRescisao->motivo_id == 7) {
            $admissao->outromotivo = $admissao->Feedback->MotivoRescisao->outro;
        };
        $admissao->aviso = !is_null($admissao->Feedback->TipoAviso) ? $admissao->Feedback->TipoAviso->tipo_aviso_id : "";

        $admissao->classificacao = "";
        $admissao->quem_classificou = "";
        $admissao->observacoes = "";
        $admissao->preenchido_por = "";


//        $admissao->deu_baixa_epi = !is_null($admissao->deu_baixa_epi) ? $admissao->deu_baixa_epi : "";
        $admissao->cipa = !is_null($admissao->cipa) ? $admissao->cipa : false;

        if (!is_null($admissao->Feedback->ClassificacaoRescisao)) {
            $admissao->classificacao = $admissao->Feedback->ClassificacaoRescisao->classificacao_id;
            $admissao->quem_classificou = $admissao->Feedback->ClassificacaoRescisao->quem_classificou;
            $admissao->observacoes = $admissao->Feedback->ClassificacaoRescisao->observacoes;
            $admissao->preenchido_por = $admissao->Feedback->ClassificacaoRescisao->preenchido_por;
        }

        return $admissao;
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Admissao $admissao
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Admissao $admissao)
    {
        $dados = $request->input();
        $usuario_avaliando = auth()->id();
        $data_avaliacao = (new DataHora())->dataHoraInsert();

        try {
            DB::beginTransaction();
            $admissao->update([
                'cipa' => $dados['cipa'],
                'data_desmobilizacao' => $dados['data_desmobilizacao'],
                'avaliacao' => $dados['avaliacao'],
                'obs_avaliacao' => $dados['obs_avaliacao'],
                'user_avaliacao' => $usuario_avaliando,
                'responsavel_feedback' => $dados['responsavel_feedback'],
                'data_avaliacao' => $data_avaliacao,
                'data_mob' => (new DataHora())->dataHoraInsert(),
                'usuario_desmob' => auth()->id()
            ]);

            $motivo = [
                'motivo_id' => $dados['motivo'],
                'outro' => $dados['outromotivo']
            ];
            if (is_null($admissao->MotivoRescisao)) {
                $admissao->Feedback->MotivoRescisao()->create($motivo);
            } else {
                $admissao->Feedback->MotivoRescisao->update($motivo);
            }

            $aviso = [
                'tipo_aviso_id' => $dados['aviso']
            ];
            if (is_null($admissao->Feedback->TipoAviso)) {
                $admissao->Feedback->TipoAviso()->create($aviso);
            } else {
                $admissao->Feedback->TipoAviso->update($aviso);
            }

            $classificacao = [
                'classificacao_id' => $dados['classificacao'],
                'quem_classificou' => $dados['quem_classificou'],
                'observacoes' => $dados['observacoes'],
                'preenchido_por' => $dados['preenchido_por'],
            ];

            if (is_null($admissao->Feedback->ClassificacaoRescisao)) {
                $admissao->Feedback->ClassificacaoRescisao()->create($classificacao);
            } else {
                $admissao->Feedback->ClassificacaoRescisao->update($classificacao);
            }

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error POS ADMISSÃO - AVALIAR : {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
//            return response()->json(['msg' => $msg], 400);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }

    public function desmobilizar(Request $request)
    {
        $dados = $request->input();
        $deubaixa = $dados['deu_baixa_epi'] == 'true' ? true : false;
        $admissao = Admissao::find($request->id);

        try {
            DB::beginTransaction();
            $admissao->update([
                'alternativas' => $dados['alternativas'],
                'deu_baixa_epi' => $deubaixa,
                'pendencia' => $dados['pendencia'],
                'pendencias_quais' => $dados['pendencia'] ? isset($dados['pendencias_quais']) ? $dados['pendencias_quais'] : null : null,
                'outros' => isset($dados['outros']) ? $dados['outros'] : null,
                'preenchido_por_rh' => $dados['preenchido_por_rh'],
                'preenchido_por_adm' => $dados['preenchido_por_adm'],
                'preenchido_por_ssma' => $dados['preenchido_por_ssma'],
                'data_desmobilizacao' => (new DataHora($dados['data_desmobilizacao']))->dataInsert(),
                'usuario_desmob' => auth()->id(),
            ]);

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error POS ADMISSÃO - DESMOBILIZAR: {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
        /*
        $dados['user_id'] = auth()->id();
        $dados['data_avaliacao'] = (new DataHora())->dataInsert();

        try {
            DB::beginTransaction();
            $admissao->update($dados);
            $motivo = [
                'motivo_id' => $dados['motivo'],
                'outro' => $dados['outromotivo']
            ];
            if (is_null($admissao->MotivoRescisao)) {
                $admissao->MotivoRescisao()->create($motivo);
            } else {
                $admissao->MotivoRescisao->update($motivo);
            }

            $aviso = [
                'tipo_aviso_id' => $dados['aviso']
            ];
            if (is_null($admissao->Feedback->TipoAviso)) {
                $admissao->Feedback->TipoAviso()->create($aviso);
            } else {
                $admissao->Feedback->TipoAviso->update($aviso);
            }

            $classificacao = [
                'classificacao_id' => $dados['classificacao'],
                'quem_classificou' => $dados['quem_classificou'],
                'observacoes' => $dados['observacoes'],
                'preenchido_por' => $dados['preenchido_por'],
            ];
            if (is_null($admissao->Feedback->ClassificacaoRescisao)) {
                $admissao->Feedback->ClassificacaoRescisao()->create($classificacao);
            } else {
                $admissao->Feedback->ClassificacaoRescisao->update($classificacao);
            }

            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error POS ADMISSÃO: {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => $msg], 400);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }*/

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Admissao $admissao
     * @return \Illuminate\Http\Response
     */
    public function destroy(Admissao $admissao)
    {
        //
    }

    public function filtro(Request $request)
    {
        $resultado = FeedbackCurriculo::whereHas('Admissao', function ($q) use ($request) {
            $q->whereIn('status', ['ADMITIDO', 'DEMITIDO']);
            if ($request->filled('campoArea')) {
                $q->whereAreaEtiquetaId($request->campoArea);
            }
            if ($request->filled('campoCargo')) {
                $q->where('cargo', 'like', '%' . $request->campoCargo . '%');
            }
        })->with(
            'Admissao:id,feedback_id,area_etiqueta_id,cargo,data_admissao',
            'Admissao.AreaEtiqueta', 'Curriculo:id,nome,cpf,nascimento,rg,orgao_expeditor',
            'Demissao.motivoRescisao',
            'Empresa',
            'VagaSelecionada',
            'EntrevistaDesligamento'
        );

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->campoBusca . '%');
            });
        }

        if ($request->filled('campoVaga')) {
            $resultado->whereHas('VagaSelecionada', function ($query) use ($request) {
                $query->whereId($request->campoVaga);
            });
        }

        if ($request->filled('campoUf')) {
            $resultado->whereHas('Curriculo', function ($q) use ($request) {
                $q->whereUfVaga($request->campoUf);
            });
        }

        if ($request->filled('campoCPF')) {
            $resultado->whereHas('Curriculo', function ($q) use ($request) {
                $q->whereCpf($request->campoCPF);
            });
        }

        if ($request->filled('campoFeedback')) {
            if ($request->campoFeedback == "nao") {
                $resultado->whereDoesntHave('EntrevistaDesligamento');
            } else {
                $resultado->whereHas('EntrevistaDesligamento');
            }
        }

        if ($request->filled('status')) {
            if ($request->status == 'admitidos') {
                $resultado->whereHas('Admissao', function ($q) {
                    $q->where('status', Admissao::STATUS_ADMISSAO_ADMITIDO);
                })->whereDoesntHave('Demissao');
            }

            if ($request->status == 'demitidos') {
                $resultado->whereHas('Admissao', function ($q) {
                    $q->where('status', Admissao::STATUS_DEMITIDO);
                })->Has('Demissao')->with('Demissao');
            }
        }

        return $resultado->filtrarPorCnpjECentroCusto($request)->orderByDesc('updated_at');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function atualizar(Request $request)
    {
        // Obtenção dos dados
        $resultado = $this->filtro($request);
        $formulario = $this->getFormularioPosAdmissao(auth()->user()->empresa_id);
        $formulario_vazio = $this->getFormularioVazio($formulario);
        $cc = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);

        // Paginação do resultado
        $resultadoPaginado = $resultado->paginate($request->get('pages', 15));

        // Transformação dos itens
        $items = $resultadoPaginado->getCollection()->map(function ($item) use ($cc) {
            return $this->transformItem($item, $cc);
        });

        // Resposta JSON
        return response()->json([
            'atual' => $resultadoPaginado->currentPage(),
            'ultima' => $resultadoPaginado->lastPage(),
            'total' => $resultadoPaginado->total(),
            'dados' => [
                'items' => $items,
                'cc' => $cc,
                'motivos_rescisoes' => $this->getMotivosRescisoes(),
                'tipos_rescisoes' => $this->getTiposRescisoes(),
                'classificacoes_rescisoes' => $this->getClassificacoesRescisoes(),
                'formulario' => $formulario,
                'form_limpo' => $formulario_vazio,
                'posadmissao_form_adm' => auth()->user()->can('posadmissao_form_adm'),
                'posadmissao_form_rh' => auth()->user()->can('posadmissao_form_rh'),
                'posadmissao_form_ssma' => auth()->user()->can('posadmissao_form_ssma'),
            ]
        ]);
    }

    /**
     * Obtém o formulário de checklist de pós-admissão
     */
    protected function getFormularioPosAdmissao($empresaId)
    {
        return Formulario::whereEmpresaId($empresaId)
            ->whereTitulo('Formulario CheckList Pos Admissão')
            ->with('Setores.Alternativas')
            ->first();
    }

    /**
     * Cria uma coleção de formulário vazio
     */
    protected function getFormularioVazio($formulario)
    {
        return $formulario ? $formulario->Setores->flatMap(function ($setor) {
            return $setor->alternativas->mapWithKeys(function ($alternativa) {
                return [$alternativa->id => false];
            });
        }) : collect();
    }

    /**
     * Transforma um item incluindo dados do centro de custo
     */
    protected function transformItem($item, $cc)
    {
        if ($item->admissao) {
            $cc_colaborador = collect($cc['centros_custos'])
                ->collapse()
                ->where('id', $item->admissao->centro_custo_id)
                ->first();

            $item->admissao->emp_cnpj = $cc_colaborador['cnpj_format'] ?? null;
            $item->admissao->emp_nome_fantasia = $cc_colaborador['nome_fantasia'] ?? null;
            $item->admissao->emp_centro_custo = $cc_colaborador['label'] ?? null;
            $item->admissao->emp_tipo = $cc_colaborador['matriz'] ? 'Matriz' : 'Filial';
        }

        return $item;
    }

    /**
     * Obtém os motivos de rescisão ativos (pode ser cacheado)
     */
    protected function getMotivosRescisoes()
    {
        return Cache::remember('motivos_rescisoes_ativos', 60, function () {
            return MotivoRescisao::whereAtivo(true)->get();
        });
    }

    /**
     * Obtém os tipos de aviso ativos (pode ser cacheado)
     */
    protected function getTiposRescisoes()
    {
        return Cache::remember('tipos_rescisoes_ativos', 60, function () {
            return TipoAviso::whereAtivo(true)->get();
        });
    }

    /**
     * Obtém as classificações de rescisão ativas (pode ser cacheado)
     */
    protected function getClassificacoesRescisoes()
    {
        return Cache::remember('classificacoes_rescisoes_ativas', 60, function () {
            return ClassificacaoRescisao::whereAtivo(true)->orderBy('classe')->get();
        });
    }


    public function export(Request $request)
    {
        $filtros = $request->input();

        $query = FeedbackCurriculo::has('Demissao')
            ->select([
                'id', 'curriculo_id', 'empresa_id', 'vagas_abertas_id', 'vaga_id'
            ])->filtrarPorCnpjECentroCusto($request)
            ->with('Admissao:id,feedback_id,area_etiqueta_id,cargo,data_admissao,salario,centro_custo_id',
                'Admissao.AreaEtiqueta',
                'Admissao.CentroCusto',
                'Curriculo:id,nome,cpf,nascimento,rg,orgao_expeditor',
                'Demissao.motivoRescisao',
                'VagaSelecionada',
                'EntrevistaDesligamento')
            ->whereHas('Admissao', function ($q) {
                $q->where('status', Admissao::STATUS_DEMITIDO);
            }
            )->Has('Demissao')
            ->with('Demissao');


        if (count($filtros['selecionados']) > 0) {
            $resultado = $query->whereIn('id', $filtros['selecionados'])->get();
        } else {

            if ($request->filled('campoFeedback')) {
                if ($request->campoFeedback == "nao") {
                    $query->whereDoesntHave('EntrevistaDesligamento');
                } else {
                    $query->whereHas('EntrevistaDesligamento');
                }
            }

            $resultado = $query->get();
        }


        $cc = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);
        $resultado = collect($resultado)->transform(function ($item) use ($cc) {
            $cc_colaborador = collect($cc['centros_custos'])->collapse()->where('id', $item->admissao->centro_custo_id)->first();
            $item->admissao->emp_cnpj = "";
            $item->admissao->emp_nome_fantasia = "";
            $item->admissao->emp_centro_custo = "";
            $item->admissao->emp_tipo = "";

            if ($cc_colaborador) {
                $item->admissao->emp_cnpj = $cc_colaborador['cnpj_format'];
                $item->admissao->emp_nome_fantasia = $cc_colaborador['nome_fantasia'];
                $item->admissao->emp_centro_custo = $cc_colaborador['label'];
                $item->admissao->emp_tipo = $cc_colaborador['matriz'] ? 'Matriz' : 'Filial';
            }

            return $item;
        });

        $entrevista = [
            "entrevista_superior_imediato",
            "entrevista_motivo",
            "entrevista_trabalharia_novamente",
            "entrevista_contr_melhoria",
            "entrevista_relacao_interpessoal",
            "entrevista_recursos_fisicos",
            "entrevista_valores_normas",
            "entrevista_planejamento",
            "entrevista_sob_superior_imediato",
            "entrevista_direcao_empresa",
            "entrevista_oportunidades",
            "entrevista_salario_beneficio",
            "entrevista_atividade",
            "entrevista_comentarios",
            "entrevista_parecer_entrevistador",
            "entrevista_pode_voltar",
            "entrevista_porque_pode_voltar",
            "entrevista_quem_entrevistou",
            "entrevista_user_entrevista",
            "entrevista_data_entrevista",
            "entrevista_preenchido_por",
        ];

        $head = [
//            'ID',
            'CNPJ',
            'Nome',
            'CPF',
//            'Área',
            'Cargo',
            'Salario',
            'Data Admissão',
            'Data Demissão',
//            'Data Entrevista',
            'Centro de Custo',
        ];

        $head = array_merge($head, $entrevista);

        $rows = [];
        $array_chunks = array_chunk($resultado->toArray(), 100);

        foreach ($array_chunks as $ch) {
            foreach ($ch as $row) {
                $data_admissao = $row['admissao'] ? (new DataHora($row['admissao']['data_admissao']))->dataCompleta() : 'NÃO INFORMADA';
                $data_desmobilizacao = $row['demissao'] ? (new DataHora($row['demissao']['data_desmobilizacao']))->dataCompleta() : 'NÃO INFORMADA';
                $entrevista = [
                    "superior_imediato" => "",
                    "motivo" => "",
                    "trabalharia_novamente" => "",
                    "contr_melhoria" => "",
                    "relacao_interpessoal" => "",
                    "recursos_fisicos" => "",
                    "valores_normas" => "",
                    "planejamento" => "",
                    "sob_superior_imediato" => "",
                    "direcao_empresa" => "",
                    "oportunidades" => "",
                    "salario_beneficio" => "",
                    "atividade" => "",
                    "comentarios" => "",
                    "parecer_entrevistador" => "",
                    "pode_voltar" => "",
                    "porque_pode_voltar" => "",
                    "quem_entrevistou" => "",
                    "user_entrevista" => "",
                    "data_entrevista" => "",
                    "preenchido_por" => "",
                ];

                if (isset($row["entrevista_desligamento"]) && auth()->user()->can('privilegio_gestao_rh')) {
                    $entrevista = [
                        "superior_imediato" => $row['entrevista_desligamento']["superior_imediato"] ?? "",
                        "motivo" => $row['entrevista_desligamento']["motivo"] ?? "",
                        "trabalharia_novamente" => $row['entrevista_desligamento']["trabalharia_novamente"] ?? "",
                        "contr_melhoria" => $row['entrevista_desligamento']["contr_melhoria"] ?? "",
                        "relacao_interpessoal" => $row['entrevista_desligamento']["relacao_interpessoal"] ?? "",
                        "recursos_fisicos" => $row['entrevista_desligamento']["recursos_fisicos"] ?? "",
                        "valores_normas" => $row['entrevista_desligamento']["valores_normas"] ?? "",
                        "planejamento" => $row['entrevista_desligamento']["planejamento"] ?? "",
                        "sob_superior_imediato" => $row['entrevista_desligamento']["sob_superior_imediato"] ?? "",
                        "direcao_empresa" => $row['entrevista_desligamento']["direcao_empresa"] ?? "",
                        "oportunidades" => $row['entrevista_desligamento']["oportunidades"] ?? "",
                        "salario_beneficio" => $row['entrevista_desligamento']["salario_beneficio"] ?? "",
                        "atividade" => $row['entrevista_desligamento']["atividade"] ?? "",
                        "comentarios" => $row['entrevista_desligamento']["comentarios"] ?? "",
                        "parecer_entrevistador" => $row['entrevista_desligamento']["parecer_entrevistador"] ?? "",
                        "pode_voltar" => $row['entrevista_desligamento']["pode_voltar"] ? "Sim" : "Não",
                        "porque_pode_voltar" => $row['entrevista_desligamento']["porque_pode_voltar"] ?? "",
                        "quem_entrevistou" => $row['entrevista_desligamento']["quem_entrevistou"] ?? "",
                        "user_entrevista" => User::select('nome')->find($row['entrevista_desligamento']["user_entrevista"])->nome,
                        "data_entrevista" => $row['entrevista_desligamento']["data_entrevista"] ?? "",
                        "preenchido_por" => $row['entrevista_desligamento']["preenchido_por"] ?? "",
                    ];
                }

                $rows[] = [
//                $row['admissao']['id'],
                    $row['admissao']['emp_nome_fantasia'],
                    $row['curriculo']['nome'],
                    $row['curriculo']['cpf'],
//                $row['admissao']['area_etiqueta_id'] ? $row['admissao']['area_etiqueta']['label'] : '',
                    $row['admissao']['cargo'],
                    $row['admissao']['salario'],
                    $data_admissao,
                    $data_desmobilizacao,
                    $row['admissao']['emp_centro_custo'] ?? "NÃO ENCONTRADO",

//                isset($row['entrevista_desligamento']) ? ((new DataHora($row['entrevista_desligamento']['data_entrevista']))->dataHoraCompleta()) : "",
                    $entrevista['superior_imediato'],
                    $entrevista['motivo'],
                    $entrevista['trabalharia_novamente'],
                    $entrevista['contr_melhoria'],
                    $entrevista['relacao_interpessoal'],
                    $entrevista['recursos_fisicos'],
                    $entrevista['valores_normas'],
                    $entrevista['planejamento'],
                    $entrevista['sob_superior_imediato'],
                    $entrevista['direcao_empresa'],
                    $entrevista['oportunidades'],
                    $entrevista['salario_beneficio'],
                    $entrevista['atividade'],
                    $entrevista['comentarios'],
                    $entrevista['parecer_entrevistador'],
                    $entrevista['pode_voltar'],
                    $entrevista['porque_pode_voltar'],
                    $entrevista['quem_entrevistou'],
                    $entrevista['user_entrevista'],
                    $entrevista['data_entrevista'],
                    $entrevista['preenchido_por'],
                ];

            }
        }

        return response()->json(['head' => $head, 'rows' => $rows]);

//        JobExportaPosAdmissao::dispatch(auth()->id(), auth()->user()->empresa_id, $filtros);
//        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }

    public function entrevistar(Request $request)
    {
        $this->authorize('posadmissao_avaliar_insert');
        $dados = $request->input();

        $dados['entrevista_desligamento']['feedback_id'] = $dados['feedback_id'];
        $dados['entrevista_desligamento']['user_entrevista'] = auth()->id();
        $dados['entrevista_desligamento']['data_entrevista'] = (new DataHora())->dataHoraInsert();

        try {
            DB::beginTransaction();
            EntrevistaDesligamento::create($dados['entrevista_desligamento']);
            DB::commit();
            return response()->json(['msg' => 'Entrevista realizada com sucesso!'], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error POS ADMISSÃO - ENTREVISTAR : {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function entrevistarUpdate(Request $request, EntrevistaDesligamento $entrevista)
    {
        $this->authorize('posadmissao_avaliar_update');
        $dados = $request->input();
        $dados['user_entrevista'] = auth()->id();
        $dados['data_entrevista'] = (new DataHora())->dataHoraInsert();

        try {
            DB::beginTransaction();
            $entrevista->update($dados);
            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error POS ADMISSÃO - ENTREVISTAR UPDATE : {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }

    public function removerDemissao(Request $request)
    {
        $this->authorize('privilegio_gestao_rh');
        $dados = $request->input();
        try {
            DB::beginTransaction();
            $admissao = Admissao::whereFeedbackId($dados['feedback_id'])
                ->whereHas('Demissao')
                ->first();
            $admissao->update(['status' => Admissao::STATUS_ADMISSAO_ADMITIDO]);
            $demissao = Demissao::whereFeedbackId($admissao->feedback_id)->first();
            $demissao->delete();
            User::find($dados['colaborador_id'])->update([
                'ativo' => true
            ]);
            AuditoriaInterna::create($dados);
            DB::commit();
            return response()->json([], 201);
        } catch (\Exception $e) {
            DB::rollback();
            $msg = "error POS ADMISSÃO - REMOVER DEMISSÃO : {$e->getMessage()} , {$e->getCode()}, {$e->getLine()} | Usuario: " . auth()->user()->nome;
            \Log::debug($msg);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }

    }
}
