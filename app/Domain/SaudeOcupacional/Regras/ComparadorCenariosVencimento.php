<?php

namespace App\Domain\SaudeOcupacional\Regras;

use App\Domain\SaudeOcupacional\Colaboradores\ColaboradoresAtivosQuery;
use App\Services\ControleExames\AsoPeriodicidadeService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

/**
 * Compara o vencimento dos ASOs atuais em dois cenários (regras + riscos por cargo).
 * Base da prévia de impacto de regras e da publicação de uma versão do PGR.
 */
class ComparadorCenariosVencimento
{
    public const LIMITE_AVALIADOS = 50000;

    public const MAX_EXEMPLOS = 5;

    private const LOTE = 500;

    public function __construct(
        private readonly MotorPeriodicidadeAso $motor,
        private readonly PerfisColaboradoresRepositorio $perfis,
        private readonly AsoPeriodicidadeService $periodicidade
    ) {
    }

    /**
     * @param  list<int>  $tipos  tipos de exame avaliados
     * @param  list<RegraPeriodicidade>  $regrasAntes
     * @param  list<RegraPeriodicidade>  $regrasDepois
     * @param  array<int, list<int>>|null  $riscosAntes  null = riscos do PGR vigente
     * @param  array<int, list<int>>|null  $riscosDepois  null = riscos do PGR vigente
     * @return array{avaliados: int, alterados: int, antecipados: int, adiados: int, exemplos: list<array<string, string>>, limite_atingido: bool}
     */
    public function comparar(
        int $empresaId,
        array $tipos,
        array $regrasAntes,
        array $regrasDepois,
        ?array $riscosAntes = null,
        ?array $riscosDepois = null
    ): array {
        $resultado = ['avaliados' => 0, 'alterados' => 0, 'antecipados' => 0, 'adiados' => 0, 'exemplos' => [], 'limite_atingido' => false];

        $padroes = [];
        foreach (array_unique(array_map('intval', $tipos)) as $tipoId) {
            $config = $this->periodicidade->resolverConfig($empresaId, $tipoId);
            if ($config !== null) {
                $padroes[$tipoId] = Periodicidade::deArray($config);
            }
        }
        if ($padroes === []) {
            return $resultado;
        }

        $exemplos = [];
        $ativos = (new ColaboradoresAtivosQuery())->daEmpresa($empresaId)->select('fc.id');

        DB::table('examesesmts as e')
            ->join('exame_funcionarios as ef', 'ef.id', '=', 'e.exame_funcionario_id')
            ->where('e.empresa_id', $empresaId)
            ->where('e.atual', true)
            ->where('e.exame_realizado', true)
            ->whereNotNull('e.data_realizacao')
            ->whereNotNull('e.data_vencimento')
            ->whereIn('ef.exame_tipo_id', array_keys($padroes))
            ->whereIn('e.feedback_id', $ativos)
            ->select(['e.id', 'e.feedback_id', 'e.data_realizacao', 'ef.exame_tipo_id'])
            ->chunkById(self::LOTE, function ($linhas) use (&$resultado, &$exemplos, $padroes, $regrasAntes, $regrasDepois, $riscosAntes, $riscosDepois) {
                $perfis = $this->perfis->carregarVarios($linhas->pluck('feedback_id')->map(fn ($id) => (int) $id)->all());

                foreach ($linhas as $linha) {
                    $tipoId = (int) $linha->exame_tipo_id;
                    $perfil = $perfis[(int) $linha->feedback_id] ?? null;
                    $realizacao = CarbonImmutable::parse($linha->data_realizacao);

                    $antes = $this->motor->calcular(
                        $realizacao,
                        $padroes[$tipoId],
                        $this->perfilNoCenario($perfil, $riscosAntes),
                        $this->doTipo($regrasAntes, $tipoId)
                    )->vencimento;
                    $depois = $this->motor->calcular(
                        $realizacao,
                        $padroes[$tipoId],
                        $this->perfilNoCenario($perfil, $riscosDepois),
                        $this->doTipo($regrasDepois, $tipoId)
                    )->vencimento;

                    $resultado['avaliados']++;
                    if (!$antes->equalTo($depois)) {
                        $resultado['alterados']++;
                        $depois->lessThan($antes) ? $resultado['antecipados']++ : $resultado['adiados']++;
                        if (count($exemplos) < self::MAX_EXEMPLOS) {
                            $exemplos[] = ['feedback_id' => (int) $linha->feedback_id, 'tipo_id' => $tipoId, 'antes' => $antes, 'depois' => $depois];
                        }
                    }
                }

                if ($resultado['avaliados'] >= self::LIMITE_AVALIADOS) {
                    $resultado['limite_atingido'] = true;

                    return false;
                }

                return true;
            }, 'e.id', 'id');

        $resultado['exemplos'] = $this->montarExemplos($exemplos);

        return $resultado;
    }

    /**
     * @param  array<int, list<int>>|null  $riscos
     */
    private function perfilNoCenario(?PerfilColaborador $perfil, ?array $riscos): ?PerfilColaborador
    {
        return $perfil && $riscos !== null ? $perfil->comRiscosDe($riscos) : $perfil;
    }

    /**
     * @param  list<RegraPeriodicidade>  $regras
     * @return list<RegraPeriodicidade>
     */
    private function doTipo(array $regras, int $tipoId): array
    {
        return array_values(array_filter($regras, fn (RegraPeriodicidade $r) => $r->valeParaTipo($tipoId)));
    }

    /**
     * @param  list<array{feedback_id: int, tipo_id: int, antes: CarbonImmutable, depois: CarbonImmutable}>  $exemplos
     * @return list<array<string, string>>
     */
    private function montarExemplos(array $exemplos): array
    {
        if ($exemplos === []) {
            return [];
        }

        $nomes = DB::table('feedback_curriculos as fc')
            ->join('curriculos as c', 'c.id', '=', 'fc.curriculo_id')
            ->whereIn('fc.id', array_column($exemplos, 'feedback_id'))
            ->pluck('c.nome', 'fc.id');
        $tipos = DB::table('exame_tipos')->whereIn('id', array_column($exemplos, 'tipo_id'))->pluck('label', 'id');

        return array_map(fn (array $ex) => [
            'colaborador' => (string) ($nomes[$ex['feedback_id']] ?? '—'),
            'tipo' => (string) ($tipos[$ex['tipo_id']] ?? '—'),
            'vencimento_atual_br' => $ex['antes']->format('d/m/Y'),
            'vencimento_novo_br' => $ex['depois']->format('d/m/Y'),
        ], $exemplos);
    }
}
