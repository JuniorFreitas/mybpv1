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
use Illuminate\Support\Facades\DB;

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
            ->where('admissoes.status', Admissao::STATUS_ADMISSAO_ADMITIDO)
            ->whereHas('Feedback')
            ->join('feedback_curriculos as feedback', 'feedback.id', '=', 'admissoes.feedback_id')
            ->join('curriculos as curriculo', 'curriculo.id', '=', 'feedback.curriculo_id')
            ->with([
                'Feedback:id,curriculo_id,vagas_abertas_id',
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
            ])
            ->orderBy('admissoes.centro_custo_id')
            ->orderBy('curriculo.nome');

        if ($request->filled('campoCnpj') || $request->filled('campoCentroCusto')) {
            self::aplicarFiltroCnpjCentroCusto($resultado, $request);
        }

        if ($request->filled('campoBusca')) {
            $busca = trim((string) $request->campoBusca);
            $resultado->where(function ($q) use ($busca) {
                $q->where('curriculo.nome', 'like', '%' . $busca . '%');
                if (ctype_digit($busca)) {
                    $q->orWhere('curriculo.id', (int) $busca);
                }
            });
        }

        if ($request->filled('campoTipoAdmissao')) {
            $resultado->where('admissoes.tipo_admissao', $request->campoTipoAdmissao);
        }

        if ($request->filled('campoCargo')) {
            $cargo = trim((string) $request->campoCargo);
            $resultado->where('admissoes.cargo', 'like', '%' . $cargo . '%');
        }

        $periodoAtivo = filter_var($request->input('campoPeriodo'), FILTER_VALIDATE_BOOLEAN)
            || $request->input('campoPeriodo') === '1'
            || $request->input('campoPeriodo') === 1;

        if ($periodoAtivo && $request->filled('dataInicio') && $request->filled('dataFim')) {
            $inicio = self::normalizarDataFiltro($request->dataInicio);
            $fim = self::normalizarDataFiltro($request->dataFim);
            if ($inicio && $fim) {
                $resultado->whereDate('admissoes.data_admissao', '>=', $inicio)
                    ->whereDate('admissoes.data_admissao', '<=', $fim);
            }
        }

        return $resultado;
    }

    /**
     * Mesma regra de Treinamentos / AdmissaoController (CNPJ + centro de custo).
     */
    protected static function aplicarFiltroCnpjCentroCusto($resultado, Request $request): void
    {
        $temCnpj = $request->filled('campoCnpj');
        $temCentroCusto = $request->filled('campoCentroCusto');

        if ($temCnpj) {
            $centros_custos = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);
            $cnpjKey = preg_replace('/[^0-9]/', '', (string) $request->campoCnpj);
            $cc = $centros_custos['centros_custos'][$request->campoCnpj]
                ?? $centros_custos['centros_custos'][$cnpjKey]
                ?? null;

            if (!$cc || !isset($cc[0])) {
                $resultado->whereRaw('1 = 0');
                return;
            }

            $cc = collect($cc);

            if (!$temCentroCusto) {
                if ($cc[0]['matriz']) {
                    $resultado->where(function ($query) use ($cc) {
                        $query->whereIn('admissoes.centro_custo_id', $cc->pluck('id')->toArray())
                            ->orWhereNull('admissoes.centro_custo_id');
                    })->where('admissoes.filial', false);
                } else {
                    $resultado->where(function ($query) use ($cc) {
                        $query->whereIn('admissoes.centro_custo_filial_id', $cc->pluck('filial_id')->toArray())
                            ->orWhereNull('admissoes.centro_custo_filial_id');
                    })->where('admissoes.filial', true);
                }
                return;
            }

            $campoCentroCusto = $request->campoCentroCusto != '--naoinformado--'
                ? $request->campoCentroCusto
                : null;

            if ($cc[0]['matriz']) {
                $resultado->where('admissoes.centro_custo_id', $campoCentroCusto)
                    ->where('admissoes.filial', false);
            } else {
                $resultado->where('admissoes.centro_custo_filial_id', $campoCentroCusto)
                    ->where('admissoes.filial', true);
            }
            return;
        }

        if ($temCentroCusto) {
            if ($request->campoCentroCusto === '--naoinformado--') {
                $resultado->where(function ($q) {
                    $q->whereNull('admissoes.centro_custo_id')
                        ->whereNull('admissoes.centro_custo_filial_id');
                });
            } else {
                $resultado->where(function ($q) use ($request) {
                    $q->where('admissoes.centro_custo_id', $request->campoCentroCusto)
                        ->orWhere('admissoes.centro_custo_filial_id', $request->campoCentroCusto);
                });
            }
        }
    }

    /**
     * Aceita Y-m-d ou d/m/Y.
     */
    protected static function normalizarDataFiltro($valor): ?string
    {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return null;
        }
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
            return $valor;
        }
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $valor, $m)) {
            return $m[3] . '-' . $m[2] . '-' . $m[1];
        }
        return null;
    }

    /**
     * Agregações do universo filtrado completo (não paginado) para os gráficos.
     */
    protected static function montarGraficos(Request $request): array
    {
        $base = self::filtro($request);
        $base->getQuery()->orders = null;
        $base->setEagerLoads([]);

        $tiposQuery = clone $base;
        $tipos = $tiposQuery
            ->select([
                DB::raw("COALESCE(NULLIF(TRIM(admissoes.tipo_admissao), ''), 'Não informado') as label"),
                DB::raw('COUNT(admissoes.id) as total'),
            ])
            ->groupBy(DB::raw("COALESCE(NULLIF(TRIM(admissoes.tipo_admissao), ''), 'Não informado')"))
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'label' => (string) $row->label,
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();

        $cargosQuery = clone $base;
        $cargos = $cargosQuery
            ->select([
                DB::raw("COALESCE(NULLIF(TRIM(admissoes.cargo), ''), 'Não informado') as label"),
                DB::raw('COUNT(admissoes.id) as total'),
            ])
            ->groupBy(DB::raw("COALESCE(NULLIF(TRIM(admissoes.cargo), ''), 'Não informado')"))
            ->orderByDesc('total')
            ->limit(12)
            ->get()
            ->map(fn ($row) => [
                'label' => (string) $row->label,
                'total' => (int) $row->total,
            ])
            ->values()
            ->all();

        $centrosQuery = clone $base;
        $centrosRaw = $centrosQuery
            ->select([
                'admissoes.centro_custo_id',
                DB::raw('COUNT(admissoes.id) as total'),
            ])
            ->groupBy('admissoes.centro_custo_id')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        $labelsCc = CentroCusto::query()
            ->whereIn('id', $centrosRaw->pluck('centro_custo_id')->filter()->all())
            ->pluck('label', 'id');

        $centros = $centrosRaw
            ->map(function ($row) use ($labelsCc) {
                $id = $row->centro_custo_id;
                return [
                    'label' => $id ? (string) ($labelsCc[$id] ?? ('#' . $id)) : 'SEM CENTRO DE CUSTO',
                    'total' => (int) $row->total,
                ];
            })
            ->values()
            ->all();

        return [
            'tipos' => $tipos,
            'cargos' => $cargos,
            'centros' => $centros,
        ];
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function atualizar(Request $request)
    {
        $porPagina = (int) ($request->input('porPagina') ?: $request->input('porPag') ?: $request->input('pages') ?: 100);
        $resultado = self::filtro($request)->paginate(max(1, $porPagina));
        $cc = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);
        $graficos = self::montarGraficos($request);
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
                'cc' => $cc,
                'total_geral' => $resultado->total(),
                'graficos' => $graficos,
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
