<?php

namespace App\Domain\SaudeOcupacional\Regras;

use App\Domain\SaudeOcupacional\ModuloSaudeOcupacional;
use App\Domain\SaudeOcupacional\Pgr\PgrVigenteRepositorio;
use App\Models\SaudeRegra;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Regras ativas por empresa, com cache versionado (invalidado ao salvar/ativar/excluir regra).
 * Com o módulo desligado, devolve lista vazia: regras escondidas não mudam vencimentos.
 */
class RegrasAtivasRepositorio
{
    private const TTL_SEGUNDOS = 600;

    private const TTL_VERSAO_SEGUNDOS = 86400;

    public function __construct(private readonly PgrVigenteRepositorio $pgr = new PgrVigenteRepositorio())
    {
    }

    /**
     * @return list<RegraPeriodicidade>
     */
    public function daEmpresa(int $empresaId): array
    {
        if (!ModuloSaudeOcupacional::habilitado($empresaId)) {
            return [];
        }

        $linhas = Cache::remember($this->chave($empresaId), self::TTL_SEGUNDOS, function () use ($empresaId) {
            // Migration ainda não rodada (ou desfeita): segue sem regras em vez de quebrar o cálculo do ASO.
            if (!Schema::hasTable(SaudeRegra::TABELA)) {
                return [];
            }

            return SaudeRegra::query()
                ->where('empresa_id', $empresaId)
                ->where('ativo', true)
                ->orderBy('id')
                ->get(['id', 'nome', 'exame_tipo_ids', 'condicoes', 'unidade', 'valor'])
                ->map(fn (SaudeRegra $r) => [
                    'id' => $r->id,
                    'nome' => $r->nome,
                    'exame_tipo_ids' => $r->exame_tipo_ids,
                    'condicoes' => $r->condicoes,
                    'unidade' => $r->unidade,
                    'valor' => $r->valor,
                ])
                ->all();
        });

        $regras = array_map(fn (array $linha) => RegraPeriodicidade::deArray($linha), $linhas);

        // Padrão NR-7: com PGR vigente, exposto a risco = anual (sempre ativa, não editável).
        if ($this->pgr->vigente($empresaId) !== null) {
            $regras[] = (new TravaLegalNr7())->regraExpostoRisco();
        }

        return $regras;
    }

    /**
     * Só as regras cadastradas pela empresa (sem as regras de mercado).
     *
     * @return list<RegraPeriodicidade>
     */
    public function daEmpresaSemMercado(int $empresaId): array
    {
        return array_values(array_filter($this->daEmpresa($empresaId), fn (RegraPeriodicidade $r) => !$r->ehRegraDeMercado()));
    }

    /**
     * @return list<RegraPeriodicidade>
     */
    public function doTipo(int $empresaId, int $exameTipoId): array
    {
        return array_values(array_filter(
            $this->daEmpresa($empresaId),
            fn (RegraPeriodicidade $r) => $r->valeParaTipo($exameTipoId)
        ));
    }

    public function invalidar(int $empresaId): void
    {
        Cache::put($this->chaveVersao($empresaId), $this->versao($empresaId) + 1, self::TTL_VERSAO_SEGUNDOS);
    }

    private function chave(int $empresaId): string
    {
        return sprintf('saude_ocupacional:regras:%d:v%d', $empresaId, $this->versao($empresaId));
    }

    private function versao(int $empresaId): int
    {
        return (int) Cache::get($this->chaveVersao($empresaId), 1);
    }

    private function chaveVersao(int $empresaId): string
    {
        return 'saude_ocupacional:regras:versao:' . $empresaId;
    }
}
