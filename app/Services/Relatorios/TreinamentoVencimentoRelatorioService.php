<?php

namespace App\Services\Relatorios;

use App\Models\Admissao;
use App\Models\CentroCusto;
use App\Models\FeedbackCurriculo;
use App\Models\SegmentoTreinamento;
use App\Models\User;
use App\Services\Treinamento\FeedbackCurriculoFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use MasterTag\DataHora;

/**
 * Regra única do relatório de vencimento de treinamentos (tela + Excel + e-mail).
 *
 * - sem demissão (Admitidos) + encaminhado a treinamento
 * - filtro por data_vencimento no período
 * - exclusão por segmento divergente da admissão
 * - PROXIMO / pintar: <= 30 dias | ATENCAO: <= 60 dias
 */
class TreinamentoVencimentoRelatorioService
{
    public const DIAS_PINTAR = 30;

    public const DIAS_PROXIMO = 30;

    public const DIAS_ATENCAO = 60;

    public const DIAS_PERIODO_PADRAO = 30;

    public const CATEGORIA_VENCIDO = 'VENCIDO';

    public const CATEGORIA_PROXIMO = 'PROXIMO';

    public const CATEGORIA_ATENCAO = 'ATENCAO';

    public const CATEGORIA_REGULAR = 'REGULAR';

    /**
     * Período do alerta por e-mail: inclui vencidos (passado) até DIAS_ATENCAO à frente.
     *
     * @return array{periodo_input: string, inicio: DataHora, fim: DataHora, inicio_str: string, fim_str: string}
     */
    public function periodoParaAlertaEmail(): array
    {
        $inicio = '2000-01-01';
        $fim = date('Y-m-d', strtotime('+' . self::DIAS_ATENCAO . ' days'));

        return $this->resolverPeriodo($inicio . ' até ' . $fim);
    }

    /**
     * @return array{periodo_input: string, inicio: DataHora, fim: DataHora, inicio_str: string, fim_str: string}
     */
    public function resolverPeriodo(?string $periodoInput): array
    {
        $periodoInput = $periodoInput
            ?: date('Y-m-d') . ' até ' . date('Y-m-d', strtotime('+' . self::DIAS_PERIODO_PADRAO . ' days'));

        $periodo = explode(' até ', $periodoInput);
        if (count($periodo) < 2) {
            $periodo = [
                date('Y-m-d'),
                date('Y-m-d', strtotime('+' . self::DIAS_PERIODO_PADRAO . ' days')),
            ];
            $periodoInput = $periodo[0] . ' até ' . $periodo[1];
        }

        $inicio = new DataHora(trim($periodo[0]) . ' 00:00:00');
        $fim = new DataHora(trim($periodo[1]) . ' 23:59:59');

        return [
            'periodo_input' => $periodoInput,
            'inicio' => $inicio,
            'fim' => $fim,
            'inicio_str' => trim($periodo[0]),
            'fim_str' => trim($periodo[1]),
        ];
    }

    /**
     * Monta a query base (mesmos filtros do Excel).
     *
     * @param  array<string, mixed>  $filtrosRequest
     */
    public function montarQuery(User $user, array $filtrosRequest, DataHora $dataInicio, DataHora $dataFim): Builder
    {
        $filtros = [
            'campoDemitido' => false,
            'campoVencimento' => 'true',
            'vencimento' => $filtrosRequest['vencimento']
                ?? (($filtrosRequest['periodo'] ?? null)
                    ?: ($dataInicio->dataInsert() . ' até ' . $dataFim->dataInsert())),
        ];

        if (!empty($filtrosRequest['campoCnpj'])) {
            $filtros['campoCnpj'] = $filtrosRequest['campoCnpj'];
        }

        if (!empty($filtrosRequest['campoCentroCusto'])) {
            $filtros['campoCentroCusto'] = $filtrosRequest['campoCentroCusto'];
        }

        // Job autentica o usuário antes; na tela Auth já existe.
        $filter = Auth::check()
            ? FeedbackCurriculoFilter::make()
            : FeedbackCurriculoFilter::forUser($user->id);

        $filter->apply($filtros);
        $query = $filter->getQuery();

        $segmentoId = $filtrosRequest['segmento_treinamento_id'] ?? null;
        if (!empty($segmentoId)) {
            $query->whereHas('Admissao', function ($q) use ($segmentoId) {
                $q->where('segmento_treinamento_id', $segmentoId);
            });
        }

        return $query->with([
            'Treinamento.Vencimentos' => function ($q) use ($dataInicio, $dataFim) {
                $q->whereBetween('treinamento_vencimento.data_vencimento', [
                    $dataInicio->dataInsert(),
                    $dataFim->dataInsert(),
                ]);
            },
            'Admissao.SegmentoTreinamento:id,nome,slug',
            'VagaSelecionada',
            'Curriculo',
        ]);
    }

    /**
     * @param  array|\Illuminate\Support\Collection  $cc
     * @return array{itens: Collection, cc: mixed, periodo_consultado: string, total_registros: int}
     */
    public function listarParaTela(User $user, array $filtrosRequest): array
    {
        $periodo = $this->resolverPeriodo($filtrosRequest['periodo'] ?? null);
        $filtrosRequest['vencimento'] = $periodo['inicio_str'] . ' até ' . $periodo['fim_str'];
        $filtrosRequest['periodo'] = $periodo['periodo_input'];

        $cc = (new CentroCusto())->listaCentroCustoPorCnpj($user->empresa_id);
        $dados = $this->montarQuery($user, $filtrosRequest, $periodo['inicio'], $periodo['fim'])->get();

        $resultado = collect();
        foreach ($dados as $feedback) {
            $item = $this->mapearItemTela($feedback, $cc);
            if ($item !== null) {
                $resultado->push($item);
            }
        }

        $resultado = $resultado->transform(function ($item) {
            $tCollect = collect($item['treinamentos']);
            $item['pintar'] = $tCollect->where('pintar', true)->count() === $tCollect->count() && $tCollect->isNotEmpty();
            $item['count_pintar'] = $tCollect->where('pintar', true)->count();

            return $item;
        })->sortByDesc('count_pintar')
            ->sortBy('pintar', SORT_REGULAR, true)
            ->values();

        return [
            'itens' => $resultado,
            'cc' => $cc,
            'periodo_consultado' => $periodo['periodo_input'],
            'total_registros' => $resultado->count(),
        ];
    }

    /**
     * @param  array|\Illuminate\Support\Collection  $cc
     * @return Collection<int, array{base: array, treinamento: array, categoria: string, status: string}>
     */
    public function mapearLinhasExcel(FeedbackCurriculo $feedback, $cc): Collection
    {
        $item = $this->mapearItemTela($feedback, $cc);
        if ($item === null) {
            return collect();
        }

        return collect($item['treinamentos'])->map(function (array $treinamento) use ($item) {
            $diasVencer = (int) $treinamento['dias_vencer'];

            return [
                'base' => [
                    'nome' => $item['nome'],
                    'cargo' => $item['cargo'],
                    'emp_cnpj' => $item['emp_cnpj'],
                    'emp_nome_fantasia' => $item['emp_nome_fantasia'],
                    'emp_centro_custo' => $item['emp_centro_custo'],
                    'segmento' => $item['segmento'],
                    'tipo' => $item['tipo'],
                ],
                'treinamento' => $treinamento,
                'categoria' => $this->determinarCategoria($diasVencer),
                'status' => $diasVencer < 0 ? 'Vencido' : 'A vencer',
            ];
        });
    }

    public function determinarCategoria(int $diasVencer): string
    {
        if ($diasVencer < 0) {
            return self::CATEGORIA_VENCIDO;
        }
        if ($diasVencer <= self::DIAS_PROXIMO) {
            return self::CATEGORIA_PROXIMO;
        }
        if ($diasVencer <= self::DIAS_ATENCAO) {
            return self::CATEGORIA_ATENCAO;
        }

        return self::CATEGORIA_REGULAR;
    }

    /**
     * @param  array|\Illuminate\Support\Collection  $cc
     * @return array<string, mixed>|null
     */
    public function mapearItemTela(FeedbackCurriculo $feedback, $cc): ?array
    {
        if (!$feedback->Treinamento || !$feedback->Treinamento->Vencimentos->isNotEmpty()) {
            return null;
        }

        $segmentoId = $feedback->Admissao
            ? ($feedback->Admissao->segmento_treinamento_id ?? SegmentoTreinamento::getIdAlumar())
            : SegmentoTreinamento::getIdAlumar();

        $vencimentos = collect();
        foreach ($feedback->Treinamento->Vencimentos as $vencimento) {
            if ($segmentoId && $vencimento->segmento_treinamento_id !== null
                && (int) $vencimento->segmento_treinamento_id !== (int) $segmentoId) {
                continue;
            }

            $diasVencer = DataHora::diferencaDias(
                (new DataHora())->dataInsert(),
                $vencimento->pivot->data_vencimento
            );

            $vencimentos->push([
                'vencimento_id' => $vencimento->id,
                'label' => $vencimento->label ?? 'Treinamento não encontrado',
                'descricao' => $vencimento->descricao ?? '',
                'data_treinamento' => $vencimento->pivot->data_treinamento,
                'data_vencimento' => $vencimento->pivot->data_vencimento,
                'dias_vencer' => $diasVencer,
                'pintar' => $diasVencer <= self::DIAS_PINTAR,
                'categoria' => $this->determinarCategoria((int) $diasVencer),
            ]);
        }

        if ($vencimentos->isEmpty()) {
            return null;
        }

        $ccColaborador = $this->resolverCentroCusto($feedback->Admissao, $cc);

        $segmentoNome = $feedback->Admissao && $feedback->Admissao->SegmentoTreinamento
            ? $feedback->Admissao->SegmentoTreinamento->nome
            : '--';

        return [
            'nome' => $feedback->Curriculo->nome ?? 'Nome não encontrado',
            'cargo' => $feedback->VagaSelecionada->nome ?? ($feedback->Admissao->cargo ?? 'NÃO ENCONTRADO'),
            'emp_cnpj' => $ccColaborador['cnpj_format'] ?? '--',
            'emp_nome_fantasia' => $ccColaborador['nome_fantasia'] ?? '--',
            'emp_centro_custo' => $ccColaborador['label'] ?? '--',
            'emp_tipo' => ($ccColaborador['matriz'] ?? false) ? 'Matriz' : 'Filial',
            'segmento' => $segmentoNome,
            'tipo' => $feedback->tipo ?? 'N/A',
            'treinamentos' => $vencimentos->sortBy('dias_vencer')->values()->all(),
        ];
    }

    /**
     * @param  array|\Illuminate\Support\Collection  $cc
     * @return array|mixed|null
     */
    private function resolverCentroCusto(?Admissao $admissao, $cc)
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
}
