<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Jobs\Excel\Relatorios\JobExportaEfetivo;
use App\Jobs\JobExportaExcel;
use App\Jobs\JobExportaPdf;
use App\Models\Admissao;
use App\Models\CentroCusto;
use App\Models\ClienteFilial;
use App\Models\FeedbackCurriculo;
use App\Models\ResultadoIntegrado;
use App\Models\Sistema;
use Illuminate\Http\Request;

class EfetivoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\Response
     */
    public function index()
    {
        return view('g.relatorios.efetivo.index');
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

    /**
     * @param Request $request
     * @return FeedbackCurriculo|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public static function filtro(Request $request)
    {
        $resultado = Admissao::admitidos()
            ->where('admissoes.status',Admissao::STATUS_ADMISSAO_ADMITIDO)
            ->whereHas('Feedback')
            ->select(['id', 'label', 'empresa_id'])
            ->join('feedback_curriculos as feedback', 'feedback.id', '=', 'admissoes.feedback_id')
            ->join('curriculos as curriculo', 'curriculo.id', '=', 'feedback.curriculo_id')
            ->with(['Feedback:id,curriculo_id,vagas_abertas_id',
                    'Feedback.Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email,municipio_id,uf_vaga',
                    'CentroCusto.Filiais',
                    'CentroCusto',
            ])
            ->select([
                'admissoes.id',
                'admissoes.feedback_id',
                'admissoes.tipo_admissao',
                'admissoes.cargo',
                'admissoes.salario',
                'admissoes.centro_custo_id',
                'admissoes.centro_custo_filial_id',
                'admissoes.filial',
                'admissoes.status',
                'admissoes.data_admissao'
            ])->orderBy('curriculo.nome');

        if ($request->filled('campoCentrosDeCusto')) {
            if($request->campoCentrosDeCusto == 'nenhum'){
                $resultado->whereNull('centro_custo_id');
            }else{
                $resultado->whereCentroCustoId($request->campoCentrosDeCusto);
            }
        }

        return $resultado;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function atualizar(Request $request)
    {
        $resultado = self::filtro($request)->paginate($request->porPag ?: 100);
        $centros_de_custo = CentroCusto::whereAtivo(true)->orderBy('label')->get();
        $filial = new ClienteFilial();
        if ($filial->temFilial()) {
            $listaFilial = $filial->getListaFilialAtiva();
        }
        $itens = collect($resultado->items())->transform(function ($item) {
            $item->data_admissao = $item->data_admissao ?: 'NÃO INFORMADA';
            $item->salario = $item->salario ?: '0,00';
            $item->cargo = $item->cargo ?: 'NÃO INFORMADO';
            $item->tipo_admissao = $item->tipo_admissao ?: 'NÃO INFORMADA';
            $item->centro_custo_label = $item->CentroCusto ? $item->CentroCusto->label : 'NÃO INFORMADO';
            return $item;
        });

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $itens,
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
        $dados = self::filtro($request)->get()->toArray();
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
        JobExportaEfetivo::dispatch(auth()->id(), auth()->user()->empresa_id);
        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);

    }

}
