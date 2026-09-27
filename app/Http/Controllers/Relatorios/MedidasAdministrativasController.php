<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Jobs\JobExportaExcel;
use App\Models\CentroCusto;
use App\Models\MedidaAdministrativa;
use Illuminate\Http\Request;
use MasterTag\DataHora;

class MedidasAdministrativasController extends Controller
{
    public function index()
    {
        return view('g.relatorios.medidasadministrativas.index');
    }

    public function show(Request $request)
    {
        $itens = $this->coletarItens($request);
        $cc = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);

        return response()->json([
            'itens' => $itens,
            'cc' => $cc,
            'total' => count($itens),
            'graficos' => $this->montarGraficos($itens),
        ]);
    }

    public function exportExcel(Request $request)
    {
        $medidas = $this->coletarItens($request);

        $head = [
            'Nome',
            'Cargo',
            'CNPJ',
            'Local',
            'Centro de Custo',
            'Motivo',
            'Causa',
            'Data Solicitação',
            'Data Retorno',
            'Solicitante',
            'Tipo',
        ];

        $rows = [];
        foreach ($medidas as $row) {
            $rows[] = [
                $row['nome'] ?? 'Não informado',
                $row['cargo'] ?? 'Não informado',
                $row['emp_cnpj'] ?? 'Não informado',
                $row['emp_nome_fantasia'] ?? 'Não informado',
                $row['centro_custo'] ?? 'Não informado',
                $row['motivo'] ?? 'Não informado',
                $row['causa'] ?? 'Não informado',
                $row['data_solicitacao'] ?? '',
                $row['data_retorno'] ?? '',
                $row['solicitante'] ?? 'Não informado',
                $row['tipo'] ?? 'Não informado',
            ];
        }

        $nameArquivo = 'medidas_administrativas_' . \Str::slug('Medidas Administrativas') . rand(1000, 9999) . '_' . date('YmdHis') . '.xlsx';
        JobExportaExcel::dispatch(auth()->id(), 'Medidas Administrativas ', $head, $rows, $nameArquivo);

        return response()->json(['msg' => 'Estamos gerando seu arquivo excel, assim que finalizado você será notificado.']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function coletarItens(Request $request): array
    {
        $cc = (new CentroCusto())->listaCentroCustoPorCnpj(auth()->user()->empresa_id);

        return $this->filtro($request)
            ->get()
            ->map(fn ($medida) => $this->mapearItem($medida, $cc))
            ->filter()
            ->values()
            ->all();
    }

    protected function filtro(Request $request)
    {
        $query = MedidaAdministrativa::query()
            ->whereHas('Feedback', function ($q) use ($request) {
                $q->where('empresa_id', auth()->user()->empresa_id);

                if ($request->status === 'admitidos') {
                    $q->admitidos();
                } elseif ($request->status === 'demitidos') {
                    $q->demitidos();
                }

                if ($request->filled('campoBusca')) {
                    $busca = trim((string) $request->campoBusca);
                    $q->whereHas('Curriculo', function ($cq) use ($busca) {
                        $cq->where('nome', 'like', '%' . $busca . '%');
                        if (ctype_digit($busca)) {
                            $cq->orWhere('id', (int) $busca);
                        }
                    });
                }

                if ($request->filled('campoCnpj') || $this->centrosSelecionadosIds($request)) {
                    $q->whereHas('Admissao', function ($aq) use ($request) {
                        $this->aplicarFiltroCnpjCentroCusto($aq, $request);
                    });
                }
            })
            ->with([
                'Feedback:id,curriculo_id,empresa_id,vaga_id,vagas_abertas_id',
                'Feedback.Curriculo:id,nome',
                'Feedback.VagaAberta:id,vaga_id,titulo',
                'Feedback.VagaAberta.VagaSelecionada:id,nome',
                'Feedback.Admissao:id,feedback_id,cargo,centro_custo_id,centro_custo_filial_id,filial,status',
                'Feedback.Admissao.CentroCusto:id,label',
            ])
            ->orderByDesc('data_solicitacao');

        $periodoAtivo = filter_var($request->input('campoPeriodo'), FILTER_VALIDATE_BOOLEAN)
            || $request->input('campoPeriodo') === '1'
            || $request->input('campoPeriodo') === 1
            || $request->filled('periodo');

        if ($periodoAtivo) {
            $periodoRaw = (string) $request->input('periodo', '');
            if (str_contains($periodoRaw, ' até ')) {
                $periodo = explode(' até ', $periodoRaw);
                if (count($periodo) === 2 && trim($periodo[0]) !== '' && trim($periodo[1]) !== '') {
                    $dataInicio = new DataHora(trim($periodo[0]) . ' 00:00:00');
                    $dataFim = new DataHora(trim($periodo[1]) . ' 23:59:59');
                    $query->where('data_solicitacao', '>=', $dataInicio->dataHoraInsert())
                        ->where('data_solicitacao', '<=', $dataFim->dataHoraInsert());
                }
            } elseif ($request->filled('dataInicio') && $request->filled('dataFim')) {
                $inicio = $this->normalizarDataFiltro($request->dataInicio);
                $fim = $this->normalizarDataFiltro($request->dataFim);
                if ($inicio && $fim) {
                    $query->whereDate('data_solicitacao', '>=', $inicio)
                        ->whereDate('data_solicitacao', '<=', $fim);
                }
            }
        }

        if ($request->filled('campoTipo')) {
            $query->where('tipo', $request->campoTipo);
        }

        if ($request->filled('campoCausa')) {
            $query->where('causa', $request->campoCausa);
        }

        return $query;
    }

    /**
     * @param  array|\Illuminate\Support\Collection  $cc
     * @return array<string, mixed>|null
     */
    protected function mapearItem(MedidaAdministrativa $medida, $cc = []): ?array
    {
        $feedback = $medida->Feedback;
        if (!$feedback) {
            return null;
        }

        $curriculo = $feedback->Curriculo;
        $vagaAberta = $feedback->VagaAberta;
        $admissao = $feedback->Admissao;
        $ccInfo = $this->resolverCentroCusto($admissao, $cc);

        $cargo = $admissao?->cargo
            ?? $vagaAberta?->VagaSelecionada?->nome
            ?? $vagaAberta?->titulo
            ?? 'Não informado';

        $centroCusto = $ccInfo['label']
            ?? $admissao?->CentroCusto?->label
            ?? 'Não informado';

        return [
            'id' => $medida->id,
            'feedback_id' => $feedback->id,
            'curriculo_id' => $curriculo?->id,
            'nome' => $curriculo?->nome ?: 'Não informado',
            'cargo' => $cargo ?: 'Não informado',
            'centro_custo' => $centroCusto ?: 'Não informado',
            'centro_custo_id' => $admissao?->centro_custo_id,
            'emp_cnpj' => $ccInfo['cnpj_format'] ?? null,
            'emp_nome_fantasia' => $ccInfo['nome_fantasia'] ?? null,
            'motivo' => $medida->motivo ?: 'Não informado',
            'causa' => $medida->causa ?: 'Não informado',
            'data_solicitacao' => $medida->data_solicitacao ?: '',
            'data_retorno' => $medida->data_retorno ?: '',
            'solicitante' => $medida->solicitante ?: 'Não informado',
            'tipo' => $medida->tipo ?: 'Não informado',
        ];
    }

    /**
     * @param  array|\Illuminate\Support\Collection  $cc
     * @return array|mixed|null
     */
    protected function resolverCentroCusto($admissao, $cc)
    {
        if (!$admissao) {
            return null;
        }

        $centros = is_array($cc) ? ($cc['centros_custos'] ?? []) : ($cc['centros_custos'] ?? []);
        $todos = collect($centros)->collapse();

        if ($admissao->filial && $admissao->centro_custo_filial_id) {
            $encontrado = $todos->first(function ($item) use ($admissao) {
                return (int) ($item['filial_id'] ?? 0) === (int) $admissao->centro_custo_filial_id
                    || (int) ($item['id'] ?? 0) === (int) $admissao->centro_custo_filial_id;
            });
            if ($encontrado) {
                return $encontrado;
            }
        }

        if ($admissao->centro_custo_id) {
            return $todos->where('id', $admissao->centro_custo_id)->first();
        }

        return null;
    }

    /**
     * @param  array<int, array<string, mixed>>  $itens
     * @return array{tipos: array<int, array{label: string, total: int}>, causas: array<int, array{label: string, total: int}>, centros: array<int, array{label: string, total: int}>}
     */
    protected function montarGraficos(array $itens): array
    {
        $tipos = [];
        $causas = [];
        $centros = [];

        foreach ($itens as $item) {
            $tipo = $item['tipo'] ?? 'Não informado';
            $causa = $item['causa'] ?? 'Não informado';
            $cc = $item['centro_custo'] ?? 'Não informado';
            $tipos[$tipo] = ($tipos[$tipo] ?? 0) + 1;
            $causas[$causa] = ($causas[$causa] ?? 0) + 1;
            $centros[$cc] = ($centros[$cc] ?? 0) + 1;
        }

        $ordenar = function (array $mapa, int $limit = 0): array {
            arsort($mapa);
            if ($limit > 0) {
                $mapa = array_slice($mapa, 0, $limit, true);
            }
            $out = [];
            foreach ($mapa as $label => $total) {
                $out[] = ['label' => (string) $label, 'total' => (int) $total];
            }
            return $out;
        };

        return [
            'tipos' => $ordenar($tipos),
            'causas' => $ordenar($causas, 12),
            'centros' => $ordenar($centros, 15),
        ];
    }

    protected function centrosSelecionadosIds(Request $request): array
    {
        $raw = $request->input('campoCentrosCusto', $request->input('centros_selecionados'));

        if ($raw === null || $raw === '') {
            $single = $request->input('campoCentroCusto');
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
            ->filter(fn ($value) => $value !== null && $value !== '' && $value !== 'todos')
            ->map(fn ($value) => (string) $value)
            ->unique()
            ->values()
            ->all();
    }

    protected function aplicarFiltroCnpjCentroCusto($query, Request $request): void
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
                        $q->whereIn('centro_custo_id', $cc->pluck('id')->toArray())
                            ->orWhereNull('centro_custo_id');
                    })->where('filial', false);
                } else {
                    $query->where(function ($q) use ($cc) {
                        $q->whereIn('centro_custo_filial_id', $cc->pluck('filial_id')->toArray())
                            ->orWhereNull('centro_custo_filial_id');
                    })->where('filial', true);
                }
                return;
            }

            if ($cc[0]['matriz']) {
                $query->whereIn('centro_custo_id', $idsSelecionados)->where('filial', false);
            } else {
                $query->where(function ($q) use ($idsSelecionados) {
                    $q->whereIn('centro_custo_id', $idsSelecionados)
                        ->orWhereIn('centro_custo_filial_id', $idsSelecionados);
                })->where('filial', true);
            }
            return;
        }

        $query->where(function ($q) use ($idsSelecionados) {
            $q->whereIn('centro_custo_id', $idsSelecionados)
                ->orWhereIn('centro_custo_filial_id', $idsSelecionados);
        });
    }

    protected function normalizarDataFiltro($valor): ?string
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
}
