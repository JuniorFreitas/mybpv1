<?php

namespace App\Services\Lotacao;

use App\Models\CentroCustoFilial;

/**
 * A lotação (matriz ou filial) vem do centro de custo, pelo vínculo já cadastrado.
 */
class LotacaoDoCentroCusto
{
    /**
     * @return array{centro_custo_id: ?int, filial: bool, centro_custo_filial_id: ?int}
     */
    public function aPartirDoCentro(?int $centroCustoId, ?int $empresaId): array
    {
        $centroCustoId = (int) $centroCustoId;
        $empresaId = (int) $empresaId;

        if ($centroCustoId <= 0 || $empresaId <= 0) {
            return [
                'centro_custo_id' => null,
                'filial' => false,
                'centro_custo_filial_id' => null,
            ];
        }

        $vinculo = CentroCustoFilial::withoutGlobalScopes()
            ->where('empresa_id', $empresaId)
            ->where('centro_custo_id', $centroCustoId)
            ->whereNull('deleted_at')
            ->where('ativo', true)
            ->orderBy('id')
            ->first();

        return [
            'centro_custo_id' => $centroCustoId,
            'filial' => $vinculo !== null,
            'centro_custo_filial_id' => $vinculo?->id,
        ];
    }
}
