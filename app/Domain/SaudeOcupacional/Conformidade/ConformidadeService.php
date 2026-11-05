<?php

namespace App\Domain\SaudeOcupacional\Conformidade;

use App\Domain\SaudeOcupacional\Colaboradores\ColaboradoresAtivosQuery;
use App\Domain\SaudeOcupacional\Regras\MotorPeriodicidadeAso;
use App\Domain\SaudeOcupacional\Regras\PerfisColaboradoresRepositorio;
use App\Domain\SaudeOcupacional\Regras\Periodicidade;
use App\Domain\SaudeOcupacional\Regras\RegrasAtivasRepositorio;
use App\Models\ClienteConfig;
use App\Services\ControleExames\AsoPeriodicidadeService;
use App\Services\Relatorios\AsoVencimentoRelatorioService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Situação do ASO de cada colaborador ativo: em dia, vencendo, vencido ou sem ASO.
 * Fonte única do Painel, da tela Conformidade, da exportação e dos alertas.
 *
 * Último ASO válido = realizado, aprovado, atual, com vencimento (o de maior vencimento).
 * "Encaminhado em" = encaminhamento ainda sem resultado realizado (evita encaminhar duas vezes).
 */
class ConformidadeService
{
    public const STATUS_VENCIDO = 'vencido';

    public const STATUS_VENCENDO = 'vencendo';

    public const STATUS_SEM_ASO = 'sem_aso';

    public const STATUS_EM_DIA = 'em_dia';

    public const STATUS = [self::STATUS_VENCIDO, self::STATUS_VENCENDO, self::STATUS_SEM_ASO, self::STATUS_EM_DIA];

    public const POR_PAGINA_MAX = 100;

    public function __construct(
        private readonly MotorPeriodicidadeAso $motor = new MotorPeriodicidadeAso(),
        private readonly AsoVencimentoRelatorioService $relatorioAso = new AsoVencimentoRelatorioService()
    ) {
    }

    /**
     * Janela de "vencendo" = ClienteConfig.vencimento_aso (sem config: 90 dias).
     *
     * @return array{dias: int, configurada: bool}
     */
    public function janela(int $empresaId): array
    {
        $chave = ClienteConfig::withoutGlobalScopes()->where('cliente_id', $empresaId)->value('vencimento_aso');
        $periodo = $this->relatorioAso->resolverPeriodoConfig($chave !== null ? (int) $chave : null);

        return ['dias' => $periodo['dias'], 'configurada' => $periodo['chave'] !== null];
    }

    /**
     * @return array{total: int, vencido: int, vencendo: int, sem_aso: int, em_dia: int}
     */
    public function totais(int $empresaId, CarbonImmutable $hoje, ?array $filtros = null): array
    {
        $janela = $this->janela($empresaId)['dias'];
        $linha = DB::query()
            ->fromSub($this->base($empresaId, $hoje, $janela, $filtros ?? []), 'b')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as vencido", [self::STATUS_VENCIDO])
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as vencendo", [self::STATUS_VENCENDO])
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as sem_aso", [self::STATUS_SEM_ASO])
            ->selectRaw("SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as em_dia", [self::STATUS_EM_DIA])
            ->first();

        return [
            'total' => (int) ($linha->total ?? 0),
            'vencido' => (int) ($linha->vencido ?? 0),
            'vencendo' => (int) ($linha->vencendo ?? 0),
            'sem_aso' => (int) ($linha->sem_aso ?? 0),
            'em_dia' => (int) ($linha->em_dia ?? 0),
        ];
    }

    /**
     * Lista paginada (ordem: vencidos primeiro, depois por vencimento).
     *
     * @param  array{status?: string|null, busca?: string|null, vaga_id?: int|null}  $filtros
     */
    public function listar(int $empresaId, array $filtros, int $pagina, int $porPagina, CarbonImmutable $hoje): LengthAwarePaginator
    {
        $porPagina = min(max($porPagina, 10), self::POR_PAGINA_MAX);
        $janela = $this->janela($empresaId)['dias'];

        $consulta = $this->consultaFiltrada($empresaId, $hoje, $janela, $filtros);
        $total = (clone $consulta)->count();
        $linhas = $consulta
            ->orderByRaw('CASE status WHEN ? THEN 0 WHEN ? THEN 1 WHEN ? THEN 2 ELSE 3 END', [
                self::STATUS_VENCIDO, self::STATUS_SEM_ASO, self::STATUS_VENCENDO,
            ])
            ->orderBy('vencimento')
            ->orderBy('nome')
            ->forPage($pagina, $porPagina)
            ->get();

        return new LengthAwarePaginator($this->mapear($empresaId, $linhas, $hoje, true), $total, $porPagina, $pagina);
    }

    /**
     * Todas as linhas do filtro, em lotes (exportação e alertas). Sem motivo (mais leve).
     *
     * @param  callable(array<int, array<string, mixed>>): void  $porLote
     */
    public function percorrer(int $empresaId, array $filtros, CarbonImmutable $hoje, callable $porLote, int $lote = 500): void
    {
        $janela = $this->janela($empresaId)['dias'];

        $this->consultaFiltrada($empresaId, $hoje, $janela, $filtros)
            ->orderBy('feedback_id')
            ->chunk($lote, function ($linhas) use ($porLote, $hoje, $empresaId) {
                $porLote($this->mapear($empresaId, $linhas, $hoje, false));
            });
    }

    /**
     * @param  array{status?: string|null, busca?: string|null, vaga_id?: int|null}  $filtros
     */
    private function consultaFiltrada(int $empresaId, CarbonImmutable $hoje, int $janela, array $filtros): Builder
    {
        $consulta = DB::query()->fromSub($this->base($empresaId, $hoje, $janela, $filtros), 'b');

        if (!empty($filtros['status']) && in_array($filtros['status'], self::STATUS, true)) {
            $consulta->where('status', $filtros['status']);
        }

        return $consulta;
    }

    /**
     * Uma linha por colaborador ativo: nome, vencimento do último ASO válido e status (CASE em SQL).
     * Leve de propósito (totais, filtro, ordenação, paginação). Detalhes vêm em detalhes() só para os ids da página.
     */
    private function base(int $empresaId, CarbonImmutable $hoje, int $janela, array $filtros): Builder
    {
        $hojeStr = $hoje->toDateString();
        $limite = $hoje->addDays($janela)->toDateString();

        $vencimento = DB::table('examesesmts as e2')
            ->selectRaw('MAX(e2.data_vencimento)')
            ->whereColumn('e2.feedback_id', 'fc.id')
            ->where('e2.empresa_id', $empresaId)
            ->where('e2.atual', true)
            ->where('e2.exame_realizado', true)
            ->where('e2.resultado->aprovado', 'Sim')
            ->whereNotNull('e2.data_vencimento');

        $interna = (new ColaboradoresAtivosQuery())->daEmpresa($empresaId)
            ->join('curriculos as c', 'c.id', '=', 'fc.curriculo_id')
            ->whereNull('c.deleted_at')
            ->select(['fc.id as feedback_id', 'c.nome'])
            ->selectSub($vencimento, 'vencimento')
            ->distinct();

        if (!empty($filtros['busca'])) {
            $termo = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], trim((string) $filtros['busca'])) . '%';
            $interna->where('c.nome', 'like', $termo);
        }
        if (!empty($filtros['vaga_id'])) {
            $interna->leftJoin('vagas_abertas as va', 'va.id', '=', 'fc.vagas_abertas_id')
                ->whereRaw('COALESCE(va.vaga_id, fc.vaga_id) = ?', [(int) $filtros['vaga_id']]);
        }

        return DB::query()
            ->fromSub($interna, 'x')
            ->select('x.*')
            ->selectRaw(
                'CASE WHEN x.vencimento IS NULL THEN ? WHEN x.vencimento < ? THEN ? WHEN x.vencimento <= ? THEN ? ELSE ? END as status',
                [self::STATUS_SEM_ASO, $hojeStr, self::STATUS_VENCIDO, $limite, self::STATUS_VENCENDO, self::STATUS_EM_DIA]
            );
    }

    /**
     * Detalhes só dos colaboradores informados: cargo vigente, último ASO (tipo e data) e encaminhamento sem resultado.
     *
     * @param  list<int>  $feedbackIds
     * @return array{cargos: array<int, object>, asos: array<int, object>, encaminhados: array<int, string>}
     */
    private function detalhes(int $empresaId, array $feedbackIds): array
    {
        $ids = $feedbackIds ?: [0];

        $cargos = DB::table('feedback_curriculos as fc')
            ->join('admissoes as a', 'a.feedback_id', '=', 'fc.id')
            ->leftJoin('vagas_abertas as va', 'va.id', '=', 'fc.vagas_abertas_id')
            ->leftJoin('vagas as v', 'v.id', '=', DB::raw('COALESCE(va.vaga_id, fc.vaga_id)'))
            ->whereIn('fc.id', $ids)
            ->whereNull('a.deleted_at')
            ->get(['fc.id', 'v.nome as cargo', 'a.cargo as cargo_admissao'])
            ->keyBy('id')
            ->all();

        // Último ASO válido de cada colaborador (maior vencimento; empate: maior id).
        $asos = [];
        DB::table('examesesmts as e')
            ->join('exame_funcionarios as ef', 'ef.id', '=', 'e.exame_funcionario_id')
            ->leftJoin('exame_tipos as t', 't.id', '=', 'ef.exame_tipo_id')
            ->whereIn('e.feedback_id', $ids)
            ->where('e.empresa_id', $empresaId)
            ->where('e.atual', true)
            ->where('e.exame_realizado', true)
            ->where('e.resultado->aprovado', 'Sim')
            ->whereNotNull('e.data_vencimento')
            ->orderByDesc('e.data_vencimento')
            ->orderByDesc('e.id')
            ->get(['e.id', 'e.feedback_id', 'e.data_realizacao', 'ef.exame_tipo_id', 't.label as tipo'])
            ->each(function ($l) use (&$asos) {
                $asos[(int) $l->feedback_id] ??= $l;
            });

        $encaminhados = DB::table('exame_funcionarios as ef')
            ->whereIn('ef.feedback_id', $ids)
            ->where('ef.empresa_id', $empresaId)
            ->whereNotExists(function ($q) {
                $q->selectRaw('1')
                    ->from('examesesmts as e3')
                    ->whereColumn('e3.exame_funcionario_id', 'ef.id')
                    ->where('e3.exame_realizado', true);
            })
            ->groupBy('ef.feedback_id')
            ->selectRaw('ef.feedback_id, MAX(ef.encaminhamento_data) as data')
            ->pluck('data', 'feedback_id')
            ->mapWithKeys(fn ($data, $id) => [(int) $id => $data])
            ->all();

        return ['cargos' => $cargos, 'asos' => $asos, 'encaminhados' => $encaminhados];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, object>  $linhas
     * @param  bool  $comMotivo  motivo do vencimento pelo motor ("Regra Idade 40+: 1 ano") — só na tela
     * @return list<array<string, mixed>>
     */
    private function mapear(int $empresaId, $linhas, CarbonImmutable $hoje, bool $comMotivo): array
    {
        $ids = $linhas->pluck('feedback_id')->map(fn ($id) => (int) $id)->all();
        $detalhes = $this->detalhes($empresaId, $ids);
        $perfis = $comMotivo ? app(PerfisColaboradoresRepositorio::class)->carregarVarios($ids) : [];
        $regras = app(RegrasAtivasRepositorio::class);
        $periodicidade = app(AsoPeriodicidadeService::class);

        return $linhas->map(function ($l) use ($hoje, $detalhes, $perfis, $regras, $periodicidade, $empresaId, $comMotivo) {
            $feedbackId = (int) $l->feedback_id;
            $vencimento = $l->vencimento ? CarbonImmutable::parse($l->vencimento)->startOfDay() : null;
            $aso = $detalhes['asos'][$feedbackId] ?? null;
            $cargo = $detalhes['cargos'][$feedbackId] ?? null;
            $encaminhadoEm = $detalhes['encaminhados'][$feedbackId] ?? null;

            $motivo = null;
            if ($comMotivo && $aso && $aso->exame_tipo_id && $aso->data_realizacao) {
                $config = $periodicidade->resolverConfig($empresaId, (int) $aso->exame_tipo_id);
                if ($config) {
                    $motivo = $this->motor->calcular(
                        CarbonImmutable::parse($aso->data_realizacao),
                        Periodicidade::deArray($config),
                        $perfis[$feedbackId] ?? null,
                        $regras->doTipo($empresaId, (int) $aso->exame_tipo_id)
                    )->motivo();
                }
            }

            return [
                'feedback_id' => $feedbackId,
                'colaborador' => (string) $l->nome,
                'cargo' => $cargo?->cargo ?: ($cargo?->cargo_admissao ? mb_strtoupper((string) $cargo->cargo_admissao) : null),
                'status' => (string) $l->status,
                'vencimento_br' => $vencimento?->format('d/m/Y'),
                'dias' => $vencimento ? (int) $hoje->diffInDays($vencimento, false) : null,
                'aso_id' => $aso ? (int) $aso->id : null,
                'ultimo_tipo' => $aso->tipo ?? null,
                'ultimo_realizado_br' => $aso && $aso->data_realizacao ? CarbonImmutable::parse($aso->data_realizacao)->format('d/m/Y') : null,
                'motivo' => $motivo,
                'encaminhado_em_br' => $encaminhadoEm ? CarbonImmutable::parse($encaminhadoEm)->format('d/m/Y') : null,
            ];
        })->values()->all();
    }
}
