<?php

namespace App\Services\CentroCusto;

use App\Models\CentroCusto;
use App\Models\ClienteFilial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class CentroCustoUnicidadeService
{
    public function __construct(
        private readonly CentroCustoCnpjSyncService $cnpjSyncService
    ) {
    }

    /**
     * Unicidade: mesmo label só bloqueia se for a mesma empresa, mesma lotação
     * (matriz/filial) e o mesmo status ativo.
     */
    public function regraLabel(
        array $dados,
        ?int $ignoreId = null,
        ?int $empresaId = null,
        ?bool $temFilial = null
    ): Unique {
        $empresaId = $empresaId ?? auth()->user()->empresa_id;
        $label = $dados['label'] ?? '';
        $ativo = (bool) ($dados['ativo'] ?? false);
        $temFilial ??= (new ClienteFilial())->temFilial($empresaId);
        $campoCnpj = $dados['campoCnpj'] ?? null;

        $clienteFilialId = null;
        $ehMatriz = true;

        if ($temFilial && !empty($campoCnpj)) {
            $listaCcs = (new CentroCusto())->listaCentroCustoPorCnpj($empresaId);
            $info = $listaCcs['cnpjs'][$campoCnpj] ?? null;
            $ehMatriz = $this->cnpjSyncService->ehCnpjMatriz($campoCnpj, $empresaId, $info);

            if (!$ehMatriz) {
                $clienteFilialId = $this->cnpjSyncService->resolverClienteFilialIdPorCnpj($campoCnpj, $empresaId);
            }
        }

        $regra = Rule::unique('centro_custos')->where(function ($query) use (
            $empresaId,
            $label,
            $ativo,
            $temFilial,
            $ehMatriz,
            $clienteFilialId
        ) {
            $query->where('empresa_id', $empresaId)
                ->where('label', $label)
                ->where('ativo', $ativo);

            if (!$temFilial) {
                return $query;
            }

            if ($ehMatriz || !$clienteFilialId) {
                return $query->whereNotExists(function ($sub) use ($empresaId) {
                    $sub->select(DB::raw(1))
                        ->from('centro_custo_filials')
                        ->whereColumn('centro_custo_filials.centro_custo_id', 'centro_custos.id')
                        ->where('centro_custo_filials.empresa_id', $empresaId)
                        ->where('centro_custo_filials.ativo', true)
                        ->whereNull('centro_custo_filials.deleted_at');
                });
            }

            return $query->whereExists(function ($sub) use ($empresaId, $clienteFilialId) {
                $sub->select(DB::raw(1))
                    ->from('centro_custo_filials')
                    ->whereColumn('centro_custo_filials.centro_custo_id', 'centro_custos.id')
                    ->where('centro_custo_filials.empresa_id', $empresaId)
                    ->where('centro_custo_filials.cliente_filial_id', $clienteFilialId)
                    ->where('centro_custo_filials.ativo', true)
                    ->whereNull('centro_custo_filials.deleted_at');
            });
        });

        if ($ignoreId) {
            $regra->ignore($ignoreId);
        }

        return $regra;
    }

    /**
     * True se já existir outro centro com mesmo label + status + lotação.
     */
    public function existeConflito(CentroCusto $centro, bool $ativoAlvo, ?int $empresaId = null): bool
    {
        $empresaId = $empresaId ?? $centro->empresa_id ?? auth()->user()->empresa_id;
        $campoCnpj = $this->cnpjSyncService->resolverCampoCnpj($centro, $empresaId);

        $validator = Validator::make(
            ['label' => $centro->label],
            [
                'label' => [
                    $this->regraLabel([
                        'label' => $centro->label,
                        'ativo' => $ativoAlvo,
                        'campoCnpj' => $campoCnpj,
                    ], $centro->id, $empresaId),
                ],
            ]
        );

        return $validator->fails();
    }
}
