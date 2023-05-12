<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Jobs\JobExportaExcel;
use App\Jobs\JobExportaPdf;
use App\Models\Admissao;
use App\Models\CentroCusto;
use App\Models\ClienteFilial;
use App\Models\FeedbackCurriculo;
use App\Models\ResultadoIntegrado;
use Illuminate\Http\Request;

class CentroDeCustoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.relatorios.centrodecusto.index');
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

    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Admissao $admissao
     * @return Admissao|ResultadoIntegrado|\Illuminate\Http\Response
     */
    public function show(FeedbackCurriculo $admissao)
    {

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Admissao $admissao
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function edit(FeedbackCurriculo $admissao)
    {

    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Admissao $admissao
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function update(Request $request, FeedbackCurriculo $admissao)
    {

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

    public function nomeCache()
    {
        return 'centrodecusto_' . auth()->id() . '_' . auth()->user()->empresa_id;
    }

    /**
     * @param Request $request
     * @return FeedbackCurriculo|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    protected function filtro(Request $request)
    {
        $resultado = CentroCusto::select(['id', 'label', 'empresa_id'])
            ->whereHas('Admissao', function ($q) {
                $q->admitidos()->whereStatus(Admissao::STATUS_ADMISSAO_ADMITIDO);
            })
            ->with(['Admissao' => function ($query) use ($request) {
                $query->whereNotNull('centro_custo_id')
                    ->where('admissoes.status',Admissao::STATUS_ADMISSAO_ADMITIDO)
                    ->admitidos()
                    ->join('feedback_curriculos as feedback', 'feedback.id', '=', 'admissoes.feedback_id')
                    ->join('curriculos as curriculo', 'curriculo.id', '=', 'feedback.curriculo_id')
                    ->with('Feedback:id,curriculo_id,vagas_abertas_id',
                        'Feedback.Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email,municipio_id,uf_vaga',
                        'Feedback.VagaAberta:id,vaga_id,titulo,municipio_id,empresa_id',
                        'Feedback.VagaAberta.VagaSelecionada:id,nome',
                        'Feedback.VagaAberta.Municipio')
                    ->select([
                        'admissoes.id',
                        'admissoes.feedback_id',
                        'admissoes.tipo_admissao',
                        'admissoes.cargo',
                        'admissoes.centro_custo_id',
                        'admissoes.centro_custo_filial_id',
                        'admissoes.filial',
                        'admissoes.status',
                        'admissoes.data_admissao'
                    ])->orderBy('curriculo.nome')

                ;
            }])->whereAtivo(true);

        if ($request->filled('campoCentrosDeCusto')) {
            $resultado->whereId($request->campoCentrosDeCusto);
        }

        $resultado = $resultado->groupBy('id')->orderBy('label');

        return $resultado;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function atualizar(Request $request)
    {
        $centros_de_custo = CentroCusto::whereAtivo(true)->orderBy('label')->get();
        $resultado = $this->filtro($request)->paginate($request->porPag ?: 50);
        $filial = new ClienteFilial();
        if ($filial->temFilial()) {
            $listaFilial = $filial->getListaFilialAtiva();
        }

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'listaFilial' => $listaFilial ?? null,
                'centros_de_custo' => $centros_de_custo
            ]
        ], 200);
    }

    /**
     * PDF
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportPdf(Request $request)
    {
        $dados = $this->filtro($request)->get()->toArray();
        $view = 'pdf.relatorio.centrosdecusto.centrosdecusto';
        $nameArquivo = "relatorio_centro_de_custo_" . rand(1000, 9999) . "_" . date('YmdHis') . ".pdf";

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

        JobExportaPdf::dispatch($usuario, "Relatório - Centro de Custo (PDF)", $dados, $nameArquivo, $view);
        return response()->json(['msg' => 'Estamos gerando seu arquivo pdf, assim que finalizado você será notificado.']);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportExcel(Request $request)
    {
        $resultado = $this->filtro($request)->get()->toArray();

        $head = [
            "Código",
            "Nome",
            "Centro de Custo",
            "Cargo",
            "Tipo de admissão",
            "Data da Admissão",
        ];

        $rows = [];

        foreach ($resultado as $row) {
            if (count($row['admissao']) > 0) {
                foreach ($row['admissao'] as $admissao) {
                    $rows[] = array(
                        $admissao['feedback']['curriculo_id'],
                        $admissao['feedback']['curriculo']['nome'],
                        $row['label'],
                        $admissao['cargo'],
                        $admissao ? $admissao['tipo_admissao'] ?: "" : "",
                        $admissao ? $admissao['data_admissao'] ?: "" : "",
                    );
                }
            }
        }

        $nameArquivo = "relatorio_centro_de_custo" . rand(1000, 9999) . "_" . date('YmdHis') . ".xlsx";
        JobExportaExcel::dispatch(auth()->id(), "Relatório - Centro de Custo", $head, $rows, $nameArquivo);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);

    }

}
