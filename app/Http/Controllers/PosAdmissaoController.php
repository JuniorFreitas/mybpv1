<?php

namespace App\Http\Controllers;

use App\Jobs\JobExportaExcel;
use App\Models\Admissao;
use App\Models\Arquivo;
use App\Models\AvaliacaoNoventaVencimento;
use App\Models\ClassificacaoRescisao;
use App\Models\DadosAdmissao;
use App\Models\Demissao;
use App\Models\EntrevistaDesligamento;
use App\Models\FeedbackCurriculo;
use App\Models\Formulario;
use App\Models\MotivoRescisao;
use App\Models\TipoAviso;
use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use MasterTag\DataHora;
use PDF;

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
            $feedback->Demissao()->create($dadosDemissao);
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

    public function atualizar(Request $request)
    {
        $resultado = $this->filtro($request)->paginate($request->pages);

        $motivosRescisoes = MotivoRescisao::whereAtivo(true)->get();
        $tipoRescisoes = TipoAviso::whereAtivo(true)->get();
        $classificacoesRescisoes = ClassificacaoRescisao::whereAtivo(true)->orderBy('classe')->get();
        $formulario = Formulario::whereEmpresaId(auth()->user()->empresa_id)->whereTitulo('Formulario CheckList Pos Admissao')->first();
        $formulario->load('Setores.Alternativas');

        $ids_form = array();
        foreach ($formulario->Setores as $f) {
            foreach ($f->alternativas as $a) {
                $ids_form[$a->id] = false;
            }
        }

        $formulario_vazio = collect($ids_form);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => ['items' => $resultado->items(),
                'motivos_rescisoes' => $motivosRescisoes,
                'tipos_rescisoes' => $tipoRescisoes,
                'classificacoes_rescisoes' => $classificacoesRescisoes,
                'formulario' => $formulario,
                'form_limpo' => $formulario_vazio
            ]
        ]);
    }

    public function filtro(Request $request)
    {
        $resultado = FeedbackCurriculo::whereHas('Admissao', function ($q) {
            $q->whereIn('status', ['PRONTO PARA ADMISSAO', 'ADMITIDO']);
        })->with('Admissao.AreaEtiqueta', 'Curriculo', 'Demissao.motivoRescisao', 'Empresa', 'VagaSelecionada', 'EntrevistaDesligamento');

        if ($request->filled('campoBusca')) {
            $resultado->whereHas('Curriculo', function ($query) use ($request) {
                $query->where('nome', 'like', '%' . $request->campoBusca . '%')->orWhere('cpf', 'like', '%' . $request->campoBusca . '%')->orWhere('id', $request->campoBusca);
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

        if ($request->filled('campoFeedback')) {
            $resultado->whereAvaliacao($request->campoFeedback);
        }

        return $resultado->orderByDesc('updated_at');

    }

    public function export(Request $request)
    {
        $resultado = $this->filtro($request)->get();

        $head = [
            'ID',
            'Nome',
            'CPF',
            'Área',
            'Cargo',
            'Data Admissão',
            'Data Demissão',
            'Status',
            'Data Entrevista',
            'Empresa',
            'Salario'
        ];

        $rows = [];

        foreach ($resultado as $row) {
            $rows[] = [
                $row->Admissao->id,
                $row->Curriculo->nome,
                $row->Curriculo->cpf,
                $row->Admissao->area_etiqueta_id ? $row->Admissao->AreaEtiqueta->label : '',
                $row->Admissao->cargo,
                (new DataHora($row->data_admissao))->dataCompleta() . ' ' . substr((new DataHora($row->data_admissao))->horaCompleta(), 0, 5),
                (new DataHora($row->data_desmobilizacao))->dataCompleta() . ' ' . substr((new DataHora($row->data_desmobilizacao))->horaCompleta(), 0, 5),
                $row->Admissao->status,
                $row->data_entrevista,
                $row->Empresa->nome_fantasia,
                $row->Admissao->salario,

            ];
        }

        $nameArquivo = "posadmissao" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "PosAdmissao", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
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
            return response()->json(['msg' => $msg], 400);
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
            return response()->json(['msg' => $msg], 400);
            return response()->json(['msg' => 'Houve um erro por favor tente novamente!'], 400);
        }
    }
}
