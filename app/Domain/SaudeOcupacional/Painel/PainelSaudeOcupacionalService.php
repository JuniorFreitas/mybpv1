<?php

namespace App\Domain\SaudeOcupacional\Painel;

use App\Domain\SaudeOcupacional\Colaboradores\ColaboradoresAtivosQuery;
use App\Domain\SaudeOcupacional\Conformidade\ConformidadeService;
use App\Domain\SaudeOcupacional\Documentos\DocumentosSstService;
use App\Domain\SaudeOcupacional\Medicos\MedicosResponsaveisService;
use App\Domain\SaudeOcupacional\Pcmso\PcmsoService;
use App\Domain\SaudeOcupacional\Pgr\PgrInventarioService;
use App\Domain\SaudeOcupacional\Pgr\PgrVigenteRepositorio;
use Illuminate\Support\Facades\Schema;
use App\Models\ClienteConfig;
use App\Services\Relatorios\AsoVencimentoRelatorioService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Números do Painel do RH ("o que fazer hoje").
 *
 * População: colaboradores admitidos/prontos para admissão e sem demissão
 * (mesma regra do relatório de vencimento de ASO).
 * Último ASO válido: realizado, aprovado, atual e com vencimento.
 *
 * Só devolve status, datas e nomes. Motivo do afastamento fica de fora
 * (texto livre que pode conter dado de saúde).
 */
class PainelSaudeOcupacionalService
{
    public const LIMITE_LISTA_AFASTADOS = 50;

    public const DIAS_VOLTANDO = 7;

    public const DIAS_LIMITE_INSS = 15;

    public function __construct(
        private readonly AsoVencimentoRelatorioService $relatorioAso = new AsoVencimentoRelatorioService()
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function resumo(int $empresaId, ?CarbonImmutable $hoje = null): array
    {
        $hoje ??= CarbonImmutable::today();

        // Data no cache evita servir números de ontem logo após a virada do dia.
        $chave = PainelCache::chave($empresaId) . ':' . $hoje->toDateString();

        return Cache::remember($chave, PainelCache::TTL_SEGUNDOS, fn () => $this->calcular($empresaId, $hoje));
    }

    /**
     * @return array<string, mixed>
     */
    public function calcular(int $empresaId, CarbonImmutable $hoje): array
    {
        $janela = $this->janelaAlerta($empresaId);

        return [
            'data_referencia' => $hoje->toDateString(),
            'data_referencia_br' => $hoje->format('d/m/Y'),
            'janela_vencendo_dias' => $janela['dias'],
            'janela_configurada' => $janela['configurada'],
            'aso' => $this->contarAso($empresaId, $hoje, $janela['dias']),
            'afastamentos' => $this->resumoAfastamentos($empresaId, $hoje),
            'programas' => $this->resumoProgramas($empresaId, $hoje),
        ];
    }

    /**
     * Médico coordenador do PCMSO e PGR vigente (Parte 2). null quando as tabelas ainda não existem.
     *
     * @return array<string, mixed>|null
     */
    private function resumoProgramas(int $empresaId, CarbonImmutable $hoje): ?array
    {
        if (!Schema::hasTable('saude_medicos') || !Schema::hasTable('pgr_inventarios')) {
            return null;
        }

        $medicos = app(MedicosResponsaveisService::class)->alertas($empresaId, $hoje);
        $pgr = app(PgrVigenteRepositorio::class)->vigente($empresaId);

        return [
            'coordenador' => $medicos['coordenador'],
            'vinculos_vencendo' => $medicos['vinculos_vencendo'],
            'pgr_vigente' => $pgr,
            'cargos_sem_avaliacao' => app(PgrInventarioService::class)->cargosComColaboradoresSemAvaliacao($empresaId, $pgr['id'] ?? null),
            'pcmso' => Schema::hasTable('pcmsos_versoes') ? app(PcmsoService::class)->alertas($empresaId, $hoje) : null,
            'documentos' => Schema::hasTable('saude_documentos') ? app(DocumentosSstService::class)->alertas($empresaId, $hoje) : null,
        ];
    }

    /**
     * @return array{total: int, sem_aso: int, vencidos: int, vencendo: int, em_dia: int}
     */
    private function contarAso(int $empresaId, CarbonImmutable $hoje, int $janelaDias): array
    {
        // Mesma fonte da tela Conformidade (os números nunca divergem).
        $totais = app(ConformidadeService::class)->totais($empresaId, $hoje);

        return [
            'total' => $totais['total'],
            'sem_aso' => $totais['sem_aso'],
            'vencidos' => $totais['vencido'],
            'vencendo' => $totais['vencendo'],
            'em_dia' => $totais['em_dia'],
        ];
    }

    /**
     * @return array{afastados_hoje: int, voltando_em_breve: int, acima_limite_inss: int, lista: list<array<string, mixed>>}
     */
    private function resumoAfastamentos(int $empresaId, CarbonImmutable $hoje): array
    {
        $hojeStr = $hoje->toDateString();

        $linhas = DB::table('afastamentos as af')
            ->join('feedback_curriculos as fc', 'fc.id', '=', 'af.feedback_id')
            ->join('curriculos as c', 'c.id', '=', 'fc.curriculo_id')
            ->whereNull('c.deleted_at')
            ->whereIn('af.feedback_id', $this->colaboradoresAtivos($empresaId)->select('fc.id'))
            ->where('af.data_inicio', '<=', $hojeStr)
            ->where('af.data_fim', '>=', $hojeStr)
            ->orderBy('af.data_fim')
            ->get(['af.id', 'af.feedback_id', 'c.nome', 'af.data_inicio', 'af.data_fim']);

        $lista = $linhas->map(function ($linha) use ($hoje) {
            $inicio = CarbonImmutable::parse($linha->data_inicio)->startOfDay();
            $fim = CarbonImmutable::parse($linha->data_fim)->startOfDay();
            $dias = (int) $inicio->diffInDays($fim) + 1;
            $retornoEm = (int) $hoje->diffInDays($fim->addDay());

            return [
                'id' => (int) $linha->id,
                'feedback_id' => (int) $linha->feedback_id,
                'colaborador' => $linha->nome,
                'data_inicio_br' => $inicio->format('d/m/Y'),
                'data_fim_br' => $fim->format('d/m/Y'),
                'dias' => $dias,
                'retorna_em_dias' => $retornoEm,
                'acima_limite_inss' => $dias > self::DIAS_LIMITE_INSS,
                'exige_aso_retorno' => $dias >= 30,
            ];
        });

        return [
            'afastados_hoje' => $lista->count(),
            'voltando_em_breve' => $lista->where('retorna_em_dias', '<=', self::DIAS_VOLTANDO)->count(),
            'acima_limite_inss' => $lista->where('acima_limite_inss', true)->count(),
            'lista' => $lista->take(self::LIMITE_LISTA_AFASTADOS)->values()->all(),
        ];
    }

    private function colaboradoresAtivos(int $empresaId): Builder
    {
        return (new ColaboradoresAtivosQuery())->daEmpresa($empresaId);
    }

    /**
     * @return array{dias: int, configurada: bool}
     */
    private function janelaAlerta(int $empresaId): array
    {
        $chave = ClienteConfig::withoutGlobalScopes()
            ->where('cliente_id', $empresaId)
            ->value('vencimento_aso');

        $periodo = $this->relatorioAso->resolverPeriodoConfig($chave !== null ? (int) $chave : null);

        return ['dias' => $periodo['dias'], 'configurada' => $periodo['chave'] !== null];
    }
}
