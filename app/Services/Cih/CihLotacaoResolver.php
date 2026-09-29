<?php

namespace App\Services\Cih;

use App\Models\CentroCusto;
use Illuminate\Http\JsonResponse;

/**
 * Resolve label de lotação (Matriz/Filial) a partir do centro de custo.
 * Mesmo formato usado no modal/listagem CIH.
 */
class CihLotacaoResolver
{
    /** @var array<int, string>|null */
    private ?array $mapa = null;

    public function __construct(private int $empresaId)
    {
    }

    public function format(?int $centroCustoId): string
    {
        if (!$centroCustoId) {
            return '';
        }

        return $this->mapa()[$centroCustoId] ?? '';
    }

    /**
     * @return array<int, string>
     */
    public function mapa(): array
    {
        if ($this->mapa !== null) {
            return $this->mapa;
        }

        $this->mapa = [];

        $lista = (new CentroCusto())->listaCentroCustoPorCnpj($this->empresaId);
        if ($lista instanceof JsonResponse) {
            return $this->mapa;
        }

        $cnpjs = collect($lista['cnpjs'] ?? []);
        foreach (collect($lista['centros_custos'] ?? []) as $cnpjKey => $centros) {
            $info = $cnpjs[$cnpjKey] ?? [];
            if (!is_array($info)) {
                $info = (array) $info;
            }

            foreach (collect($centros) as $centro) {
                $centro = (object) $centro;
                $id = (int) ($centro->id ?? 0);
                if ($id <= 0) {
                    continue;
                }

                $tipo = !empty($centro->matriz) ? 'Matriz' : 'Filial';
                $nome = $info['nome_fantasia'] ?? $info['razao_social'] ?? $centro->nome_fantasia ?? $centro->razao_social ?? null;
                $cnpj = $info['cnpj'] ?? $centro->cnpj_format ?? null;

                if ($nome && $cnpj) {
                    $this->mapa[$id] = "{$nome} - {$cnpj} ({$tipo})";
                } elseif ($nome) {
                    $this->mapa[$id] = "{$nome} ({$tipo})";
                } else {
                    $this->mapa[$id] = $tipo;
                }
            }
        }

        return $this->mapa;
    }
}
