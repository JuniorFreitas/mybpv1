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
use Illuminate\Support\Facades\DB;

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
            ->whereHas('Admissao', function ($q) use ($request) {
                $q->admitidos()->whereStatus(Admissao::STATUS_ADMISSAO_ADMITIDO);
                $this->aplicarFiltroCnpjNaAdmissao($q, $request);
            })
            ->with(['Admissao' => function ($query) use ($request) {
                $query->whereNotNull('centro_custo_id')
                    ->where('admissoes.status', Admissao::STATUS_ADMISSAO_ADMITIDO)
                    ->admitidos()
                    ->join('feedback_curriculos as feedback', 'feedback.id', '=', 'admissoes.feedback_id')
                    ->join('curriculos as curriculo', 'curriculo.id', '=', 'feedback.curriculo_id')
                    ->with(
                        'Feedback:id,curriculo_id,vagas_abertas_id',
                        'Feedback.Curriculo:id,nome,cpf,rg,orgao_expeditor,nascimento,logradouro,complemento,bairro,municipio,uf,cep,formacao,pcd,email,municipio_id,uf_vaga',
                        'Feedback.VagaAberta:id,vaga_id,titulo,municipio_id,empresa_id',
                        'Feedback.VagaAberta.VagaSelecionada:id,nome',
                        'Feedback.VagaAberta.Municipio'
                    )
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
                    ])
                    ->orderBy('curriculo.nome');

                $this->aplicarFiltroCnpjNaAdmissao($query, $request);
            }])->whereAtivo(true);

        if ($ids = $this->centrosSelecionadosIds($request)) {
            $resultado->whereIn('id', $ids);
        } elseif ($request->filled('campoCnpj')) {
            $idsCnpj = $this->idsCentrosDoCnpj($request->campoCnpj);
            if (empty($idsCnpj)) {
                $resultado->whereRaw('1 = 0');
            } else {
                $resultado->whereIn('id', $idsCnpj);
            }
        }

        $resultado = $resultado->groupBy('id')->orderBy('label');

        return $resultado;
    }

    /**
     * IDs de centros selecionados no filtro (multi).
     */
    protected function centrosSelecionadosIds(Request $request): array
    {
        $raw = $request->input('campoCentrosCusto', $request->input('centros_selecionados'));

        if ($raw === null || $raw === '') {
            $single = $request->input('campoCentroCusto', $request->input('campoCentrosDeCusto'));
            $raw = filled($single) ? [$single] : [];
        }

        if (is_string($raw)) {
            $raw = preg_split('/[|,]/', $raw) ?: [];
        }

        if (!is_array($raw)) {
            return [];
        }

        return collect($raw)
            ->map(function ($item) {
                if (is_array($item)) {
                    return $item['value'] ?? $item['id'] ?? null;
                }
                return $item;
            })
            ->filter(function ($value) {
                return $value !== null && $value !== '' && $value !== '--naoinformado--' && $value !== 'todos';
            })
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * IDs de centro de custo do CNPJ (listaCentroCustoPorCnpj).
     */
    protected function idsCentrosDoCnpj($campoCnpj): array
    {
        $centros_custos = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);
        $cnpjKey = preg_replace('/[^0-9]/', '', (string) $campoCnpj);
        $cc = $centros_custos['centros_custos'][$campoCnpj]
            ?? $centros_custos['centros_custos'][$cnpjKey]
            ?? null;

        if (!$cc) {
            return [];
        }

        $cc = collect($cc);
        if (!empty($cc[0]['matriz'])) {
            return $cc->pluck('id')->filter()->values()->all();
        }

        return $cc->pluck('id')->filter()->values()->all();
    }

    /**
     * Mesma regra de Treinamentos / Efetivo na query de Admissão.
     */
    protected function aplicarFiltroCnpjNaAdmissao($query, Request $request): void
    {
        $temCnpj = $request->filled('campoCnpj');
        $idsSelecionados = $this->centrosSelecionadosIds($request);
        $temCentros = !empty($idsSelecionados);

        if (!$temCnpj && !$temCentros) {
            return;
        }

        if ($temCnpj) {
            $centros_custos = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);
            $cnpjKey = preg_replace('/[^0-9]/', '', (string) $request->campoCnpj);
            $cc = $centros_custos['centros_custos'][$request->campoCnpj]
                ?? $centros_custos['centros_custos'][$cnpjKey]
                ?? null;

            if (!$cc || !isset($cc[0])) {
                $query->whereRaw('1 = 0');
                return;
            }

            $cc = collect($cc);

            if (!$temCentros) {
                if ($cc[0]['matriz']) {
                    $query->where(function ($q) use ($cc) {
                        $q->whereIn('admissoes.centro_custo_id', $cc->pluck('id')->toArray())
                            ->orWhereNull('admissoes.centro_custo_id');
                    })->where('admissoes.filial', false);
                } else {
                    $query->where(function ($q) use ($cc) {
                        $q->whereIn('admissoes.centro_custo_filial_id', $cc->pluck('filial_id')->toArray())
                            ->orWhereNull('admissoes.centro_custo_filial_id');
                    })->where('admissoes.filial', true);
                }
                return;
            }

            if ($cc[0]['matriz']) {
                $query->whereIn('admissoes.centro_custo_id', $idsSelecionados)
                    ->where('admissoes.filial', false);
            } else {
                $query->where(function ($q) use ($idsSelecionados) {
                    $q->whereIn('admissoes.centro_custo_id', $idsSelecionados)
                        ->orWhereIn('admissoes.centro_custo_filial_id', $idsSelecionados);
                })->where('admissoes.filial', true);
            }
            return;
        }

        $query->where(function ($q) use ($idsSelecionados) {
            $q->whereIn('admissoes.centro_custo_id', $idsSelecionados)
                ->orWhereIn('admissoes.centro_custo_filial_id', $idsSelecionados);
        });
    }

    /**
     * Agregações do universo filtrado completo (não paginado) para os gráficos.
     */
    protected function montarGraficos(Request $request): array
    {
        $base = Admissao::admitidos()
            ->where('admissoes.status', Admissao::STATUS_ADMISSAO_ADMITIDO)
            ->whereHas('Feedback')
            ->whereNotNull('admissoes.centro_custo_id')
            ->join('feedback_curriculos as feedback', 'feedback.id', '=', 'admissoes.feedback_id')
            ->join('curriculos as curriculo', 'curriculo.id', '=', 'feedback.curriculo_id')
            ->whereHas('CentroCusto', function ($q) {
                $q->whereAtivo(true);
            });

        $this->aplicarFiltroCnpjNaAdmissao($base, $request);

        if ($ids = $this->centrosSelecionadosIds($request)) {
            if (!$request->filled('campoCnpj')) {
                $base->where(function ($q) use ($ids) {
                    $q->whereIn('admissoes.centro_custo_id', $ids)
                        ->orWhereIn('admissoes.centro_custo_filial_id', $ids);
                });
            }
        } elseif ($request->filled('campoCnpj')) {
            $idsCnpj = $this->idsCentrosDoCnpj($request->campoCnpj);
            if (empty($idsCnpj)) {
                $base->whereRaw('1 = 0');
            } else {
                $base->whereIn('admissoes.centro_custo_id', $idsCnpj);
            }
        }

        $base->getQuery()->orders = null;

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

        $totalGeralQuery = clone $base;
        $totalGeral = (int) $totalGeralQuery->select(DB::raw('COUNT(admissoes.id) as aggregate'))->value('aggregate');

        return [
            'tipos' => $tipos,
            'cargos' => $cargos,
            'centros' => $centros,
            'total_geral' => $totalGeral,
        ];
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function atualizar(Request $request)
    {
        $porPagina = (int) ($request->input('porPagina') ?: $request->input('porPag') ?: $request->input('pages') ?: 50);
        $resultado = $this->filtro($request)->paginate(max(1, $porPagina));
        $cc = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);
        $graficos = $this->montarGraficos($request);

        return response()->json([
            'atual' => $resultado->currentPage(),
            'ultima' => $resultado->lastPage(),
            'total' => $resultado->total(),
            'dados' => [
                'itens' => $resultado->items(),
                'cc' => $cc,
                'graficos' => [
                    'tipos' => $graficos['tipos'],
                    'cargos' => $graficos['cargos'],
                    'centros' => $graficos['centros'],
                ],
                'total_geral' => $graficos['total_geral'],
                'total_centros' => $resultado->total(),
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
