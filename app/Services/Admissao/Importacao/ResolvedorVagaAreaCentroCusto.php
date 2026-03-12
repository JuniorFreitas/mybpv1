<?php

namespace App\Services\Admissao\Importacao;

use App\Models\AreaEtiqueta;
use App\Models\CentroCusto;
use App\Models\VagasAbertas;

/**
 * Resolve cod_vaga, cod_area e centro_custo (código ou nome) para os IDs internos da empresa.
 * Retorna ID ou estrutura de erro (não lança exceção).
 *
 * Para testes, pode-se injetar callables que substituem a busca no banco:
 *   $resolver = new ResolvedorVagaAreaCentroCusto($vagaFn, $areaFn, $ccFn);
 * Cada callable recebe (int $empresaId, string $valor) e retorna ['id' => int|null, 'erro' => string|null].
 */
class ResolvedorVagaAreaCentroCusto
{
    /** @var callable|null (int $empresaId, string $valor): array{id: int|null, erro: string|null} */
    private $vagaResolver;

    /** @var callable|null (int $empresaId, string $valor): array{id: int|null, erro: string|null} */
    private $areaResolver;

    /** @var callable|null (int $empresaId, string $valor): array{id: int|null, erro: string|null} */
    private $centroCustoResolver;

    public function __construct(?callable $vagaResolver = null, ?callable $areaResolver = null, ?callable $centroCustoResolver = null)
    {
        $this->vagaResolver = $vagaResolver;
        $this->areaResolver = $areaResolver;
        $this->centroCustoResolver = $centroCustoResolver;
    }

    /**
     * Resolve cod_vaga (ID ou nome da vaga) para vagas_abertas_id.
     *
     * @return array{id: int|null, erro: string|null}
     */
    public function resolverVaga(int $empresaId, $valor): array
    {
        $valor = $this->normalizarValor($valor);
        if ($valor === '') {
            return ['id' => null, 'erro' => 'Código ou nome da vaga é obrigatório.'];
        }

        if ($this->vagaResolver !== null) {
            return ($this->vagaResolver)($empresaId, $valor);
        }

        if ($this->ehNumerico($valor)) {
            $vagaAberta = VagasAbertas::withoutGlobalScopes()
                ->where('id', (int) $valor)
                ->where('empresa_id', $empresaId)
                ->first();
            if ($vagaAberta) {
                return ['id' => $vagaAberta->id, 'erro' => null];
            }
            return ['id' => null, 'erro' => 'Nenhuma vaga encontrada com o código informado.'];
        }

        $vagaAberta = VagasAbertas::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->whereHas('Vaga', function ($q) use ($valor) {
                $q->where('nome', $valor);
            })
            ->first();

        if ($vagaAberta) {
            return ['id' => $vagaAberta->id, 'erro' => null];
        }
        return ['id' => null, 'erro' => 'Nenhuma vaga encontrada com o nome informado. Use o código numérico ou cadastre a vaga.'];
    }

    /**
     * Resolve cod_area (ID ou label) para area_etiqueta_id.
     *
     * @return array{id: int|null, erro: string|null}
     */
    public function resolverArea(int $empresaId, $valor): array
    {
        $valor = $this->normalizarValor($valor);
        if ($valor === '') {
            return ['id' => null, 'erro' => null];
        }

        if ($this->areaResolver !== null) {
            return ($this->areaResolver)($empresaId, $valor);
        }

        if ($this->ehNumerico($valor)) {
            $area = AreaEtiqueta::withoutGlobalScopes()
                ->where('id', (int) $valor)
                ->where('empresa_id', $empresaId)
                ->first();
            if ($area) {
                return ['id' => $area->id, 'erro' => null];
            }
            return ['id' => null, 'erro' => 'Nenhuma área encontrada com o código informado.'];
        }

        $area = AreaEtiqueta::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('label', $valor)
            ->first();

        if ($area) {
            return ['id' => $area->id, 'erro' => null];
        }
        return ['id' => null, 'erro' => 'Nenhuma área encontrada com o nome informado.'];
    }

    /**
     * Resolve centro_custo (ID ou label) para centro_custo_id.
     *
     * @return array{id: int|null, erro: string|null}
     */
    public function resolverCentroCusto(int $empresaId, $valor): array
    {
        $valor = $this->normalizarValor($valor);
        if ($valor === '') {
            return ['id' => null, 'erro' => 'Centro de custo é obrigatório.'];
        }

        if ($this->centroCustoResolver !== null) {
            return ($this->centroCustoResolver)($empresaId, $valor);
        }

        if ($this->ehNumerico($valor)) {
            $cc = CentroCusto::withoutGlobalScopes()
                ->where('id', (int) $valor)
                ->where('empresa_id', $empresaId)
                ->first();
            if ($cc) {
                return ['id' => $cc->id, 'erro' => null];
            }
            return ['id' => null, 'erro' => 'Nenhum centro de custo encontrado com o código informado.'];
        }

        $cc = CentroCusto::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('label', $valor)
            ->first();

        if ($cc) {
            return ['id' => $cc->id, 'erro' => null];
        }
        return ['id' => null, 'erro' => 'Nenhum centro de custo encontrado com o nome informado. Use o código ou cadastre o centro de custo.'];
    }

    private function normalizarValor($valor): string
    {
        if ($valor === null) {
            return '';
        }
        return trim((string) $valor);
    }

    private function ehNumerico($valor): bool
    {
        return is_numeric($valor) && (string)(int) $valor === (string) $valor;
    }
}
