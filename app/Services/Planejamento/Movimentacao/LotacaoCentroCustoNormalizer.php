<?php

namespace App\Services\Planejamento\Movimentacao;

use App\Models\CentroCustoFilial;

/**
 * A filial da movimentação segue o centro de custo e não precisa ser informada à parte.
 */
class LotacaoCentroCustoNormalizer
{
    public function aplicar(
        array $dados,
        string $campoCentroCusto = 'centro_custo_id',
        string $campoFilial = 'filial',
        string $campoFilialId = 'centro_custo_filial_id'
    ): array {
        $centroCustoId = $this->idPositivo($dados[$campoCentroCusto] ?? null);
        $filialIds = $centroCustoId === null ? [] : $this->filialIdsDoCentroCusto($centroCustoId);

        return $this->aplicarComFiliais(
            $dados,
            $filialIds,
            $campoCentroCusto,
            $campoFilial,
            $campoFilialId
        );
    }

    /**
     * @param  list<int>  $filialIdsDoCentroCusto
     */
    public function aplicarComFiliais(
        array $dados,
        array $filialIdsDoCentroCusto,
        string $campoCentroCusto = 'centro_custo_id',
        string $campoFilial = 'filial',
        string $campoFilialId = 'centro_custo_filial_id'
    ): array {
        $lotacao = $this->normalizar(
            $this->idPositivo($dados[$campoCentroCusto] ?? null),
            $dados[$campoFilialId] ?? null,
            $filialIdsDoCentroCusto
        );

        $dados[$campoCentroCusto] = $lotacao['centro_custo_id'];
        $dados[$campoFilial] = $lotacao['filial'];
        $dados[$campoFilialId] = $lotacao['centro_custo_filial_id'];

        return $dados;
    }

    /**
     * @param  list<int>  $filialIdsDoCentroCusto
     * @return array{centro_custo_id: ?int, filial: bool, centro_custo_filial_id: ?int}
     */
    public function normalizar(?int $centroCustoId, mixed $centroCustoFilialId, array $filialIdsDoCentroCusto): array
    {
        if ($centroCustoId === null || $centroCustoId <= 0) {
            return [
                'centro_custo_id' => null,
                'filial' => false,
                'centro_custo_filial_id' => null,
            ];
        }

        $ids = [];
        foreach ($filialIdsDoCentroCusto as $id) {
            $id = (int) $id;
            if ($id > 0 && !in_array($id, $ids, true)) {
                $ids[] = $id;
            }
        }

        if ($ids === []) {
            return [
                'centro_custo_id' => $centroCustoId,
                'filial' => false,
                'centro_custo_filial_id' => null,
            ];
        }

        $informado = $this->idPositivo($centroCustoFilialId);

        return [
            'centro_custo_id' => $centroCustoId,
            'filial' => true,
            'centro_custo_filial_id' => ($informado !== null && in_array($informado, $ids, true))
                ? $informado
                : $ids[0],
        ];
    }

    /**
     * @return list<int>
     */
    private function filialIdsDoCentroCusto(int $centroCustoId): array
    {
        return CentroCustoFilial::query()
            ->where('centro_custo_id', $centroCustoId)
            ->orderByDesc('ativo')
            ->orderBy('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function idPositivo(mixed $valor): ?int
    {
        if ($valor === null || $valor === '' || $valor === false) {
            return null;
        }

        $id = (int) $valor;

        return $id > 0 ? $id : null;
    }
}
