<?php

namespace App\Services\Admissao\Importacao;

use App\Models\AreaEtiqueta;
use App\Models\CentroCusto;
use App\Models\VagasAbertas;
use Illuminate\Support\Facades\Log;

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
        $valorOriginal = $valor;
        $valor = $this->normalizarValor($valor);
        if ($valor === '') {
            $r = ['id' => null, 'erro' => 'Código ou nome da vaga é obrigatório.'];
            $this->debugResolucao('vaga', $empresaId, $valorOriginal, $valor, $r);
            return $r;
        }

        if ($this->vagaResolver !== null) {
            $r = ($this->vagaResolver)($empresaId, $valor);
            $this->debugResolucao('vaga', $empresaId, $valorOriginal, $valor, $r);
            return $r;
        }

        if ($this->ehNumerico($valor)) {
            $vagaAberta = VagasAbertas::withoutGlobalScopes()
                ->where('id', (int) $valor)
                ->where('empresa_id', $empresaId)
                ->first();
            if ($vagaAberta) {
                $r = ['id' => $vagaAberta->id, 'erro' => null];
                $this->debugResolucao('vaga', $empresaId, $valorOriginal, $valor, $r, 'id');
                return $r;
            }
            $r = ['id' => null, 'erro' => 'Nenhuma vaga encontrada com o código informado. Valor: "' . $valor . '".'];
            $this->debugResolucao('vaga', $empresaId, $valorOriginal, $valor, $r, 'id');
            return $r;
        }

        $vagaAberta = VagasAbertas::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->whereHas('Vaga', function ($q) use ($valor) {
                $q->where('nome', $valor);
            })
            ->first();

        if ($vagaAberta) {
            $r = ['id' => $vagaAberta->id, 'erro' => null];
            $this->debugResolucao('vaga', $empresaId, $valorOriginal, $valor, $r, 'nome');
            return $r;
        }
        $r = ['id' => null, 'erro' => 'Nenhuma vaga encontrada com o nome informado. Use o código numérico ou cadastre a vaga. Valor: "' . $valor . '".'];
        $this->debugResolucao('vaga', $empresaId, $valorOriginal, $valor, $r, 'nome');
        return $r;
    }

    /**
     * Resolve cod_area (ID ou label) para area_etiqueta_id.
     *
     * @return array{id: int|null, erro: string|null}
     */
    public function resolverArea(int $empresaId, $valor): array
    {
        $valorOriginal = $valor;
        $valor = $this->normalizarValor($valor);
        if ($valor === '') {
            $r = ['id' => null, 'erro' => null];
            $this->debugResolucao('area', $empresaId, $valorOriginal, $valor, $r);
            return $r;
        }

        if ($this->areaResolver !== null) {
            $r = ($this->areaResolver)($empresaId, $valor);
            $this->debugResolucao('area', $empresaId, $valorOriginal, $valor, $r);
            return $r;
        }

        if ($this->ehNumerico($valor)) {
            $area = AreaEtiqueta::withoutGlobalScopes()
                ->where('id', (int) $valor)
                ->where('empresa_id', $empresaId)
                ->first();
            if ($area) {
                $r = ['id' => $area->id, 'erro' => null];
                $this->debugResolucao('area', $empresaId, $valorOriginal, $valor, $r, 'id');
                return $r;
            }
            $r = ['id' => null, 'erro' => 'Nenhuma área encontrada com o código informado. Valor: "' . $valor . '".'];
            $this->debugResolucao('area', $empresaId, $valorOriginal, $valor, $r, 'id');
            return $r;
        }

        $area = AreaEtiqueta::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('label', $valor)
            ->first();

        if ($area) {
            $r = ['id' => $area->id, 'erro' => null];
            $this->debugResolucao('area', $empresaId, $valorOriginal, $valor, $r, 'label');
            return $r;
        }
        $r = ['id' => null, 'erro' => 'Nenhuma área encontrada com o nome informado. Valor: "' . $valor . '".'];
        $this->debugResolucao('area', $empresaId, $valorOriginal, $valor, $r, 'label');
        return $r;
    }

    /**
     * Resolve centro_custo (ID ou label) para centro_custo_id.
     *
     * @return array{id: int|null, erro: string|null}
     */
    public function resolverCentroCusto(int $empresaId, $valor): array
    {
        $valorOriginal = $valor;
        $valor = $this->normalizarValor($valor);
        if ($valor === '') {
            $r = ['id' => null, 'erro' => 'Centro de custo é obrigatório.'];
            $this->debugResolucao('centro_custo', $empresaId, $valorOriginal, $valor, $r);
            return $r;
        }

        if ($this->centroCustoResolver !== null) {
            $r = ($this->centroCustoResolver)($empresaId, $valor);
            $this->debugResolucao('centro_custo', $empresaId, $valorOriginal, $valor, $r);
            return $r;
        }

        if ($this->ehNumerico($valor)) {
            $cc = CentroCusto::withoutGlobalScopes()
                ->where('id', (int) $valor)
                ->where('empresa_id', $empresaId)
                ->first();
            if ($cc) {
                $r = ['id' => $cc->id, 'erro' => null];
                $this->debugResolucao('centro_custo', $empresaId, $valorOriginal, $valor, $r, 'id');
                return $r;
            }
            $r = ['id' => null, 'erro' => 'Nenhum centro de custo encontrado com o código informado. Valor: "' . $valor . '".'];
            $this->debugResolucao('centro_custo', $empresaId, $valorOriginal, $valor, $r, 'id');
            return $r;
        }

        $cc = CentroCusto::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('label', $valor)
            ->first();

        if ($cc) {
            $r = ['id' => $cc->id, 'erro' => null];
            $this->debugResolucao('centro_custo', $empresaId, $valorOriginal, $valor, $r, 'label');
            return $r;
        }
        $r = ['id' => null, 'erro' => 'Nenhum centro de custo encontrado com o nome informado. Use o código ou cadastre o centro de custo. Valor: "' . $valor . '".'];
        $this->debugResolucao('centro_custo', $empresaId, $valorOriginal, $valor, $r, 'label');
        return $r;
    }

    /**
     * Normaliza o valor para resolução: trim e, se no formato "id|descrição", usa apenas o ID (primeiro segmento).
     */
    private function normalizarValor($valor): string
    {
        if ($valor === null) {
            return '';
        }
        $valor = trim((string) $valor);
        if (str_contains($valor, '|')) {
            $partes = explode('|', $valor, 2);
            $valor = trim($partes[0]);
        }
        return $valor;
    }

    private function ehNumerico($valor): bool
    {
        return is_numeric($valor) && (string)(int) $valor === (string) $valor;
    }

    /**
     * Log de debug para cada resolução (empresa_id, valor original, valor normalizado, critério, resultado).
     * Aparece em storage/logs/laravel.log quando LOG_LEVEL=debug.
     *
     * @param array{id: int|null, erro: string|null} $resultado
     */
    private function debugResolucao(string $tipo, int $empresaId, $valorOriginal, string $valorNormalizado, array $resultado, ?string $criterio = null): void
    {
        $valorOriginalStr = $valorOriginal === null ? 'null' : '"' . trim((string) $valorOriginal) . '"';
        $resumo = $resultado['erro'] !== null
            ? 'erro: ' . $resultado['erro']
            : 'id: ' . (int) $resultado['id'];
        $ctx = [
            'tipo' => $tipo,
            'empresa_id' => $empresaId,
            'valor_original' => $valorOriginalStr,
            'valor_normalizado' => $valorNormalizado === '' ? '(vazio)' : '"' . $valorNormalizado . '"',
            'criterio' => $criterio ?? '-',
            'resultado' => $resumo,
        ];
        Log::debug('ResolvedorVagaAreaCentroCusto', $ctx);
    }
}
