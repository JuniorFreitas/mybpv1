<?php

namespace App\Services\CentroCusto;

use App\Models\CentroCusto;
use App\Models\CentroCustoFilial;
use App\Models\ClienteFilial;

class CentroCustoCnpjSyncService
{
    public function resolverCampoCnpj(CentroCusto $centro, ?int $empresaId = null): ?string
    {
        $empresaId = $empresaId ?? auth()->user()->empresaAtivaId();
        $listaCcs = (new CentroCusto())->listaCentroCustoPorCnpj($empresaId);

        foreach ($listaCcs['centros_custos'] as $cnpjKey => $grupo) {
            if ($grupo->pluck('id')->contains($centro->id)) {
                return $cnpjKey;
            }
        }

        return $this->resolverCampoCnpjMatriz($empresaId);
    }

    public function resolverCampoCnpjMatriz(?int $empresaId = null): ?string
    {
        $empresaId = $empresaId ?? auth()->user()->empresaAtivaId();
        $listaCcs = (new CentroCusto())->listaCentroCustoPorCnpj($empresaId);

        foreach ($listaCcs['cnpjs'] as $cnpjKey => $info) {
            if (!empty($info['matriz'])) {
                return $cnpjKey;
            }
        }

        $cnpjs = $listaCcs['cnpjs'] ?? [];

        return !empty($cnpjs) ? array_key_first($cnpjs) : null;
    }

    public function sincronizar(CentroCusto $centro, ?string $campoCnpj, ?int $empresaId = null): void
    {
        $empresaId = $empresaId ?? auth()->user()->empresaAtivaId();
        $clienteFilial = new ClienteFilial();

        if (!$clienteFilial->temFilial($empresaId)) {
            return;
        }

        if (empty($campoCnpj)) {
            $this->desativarAssociacoes($centro->id, $empresaId);
            $this->invalidarCache($empresaId);

            return;
        }

        $listaCcs = (new CentroCusto())->listaCentroCustoPorCnpj($empresaId);
        $info = $listaCcs['cnpjs'][$campoCnpj] ?? null;

        if (!$info) {
            throw new \InvalidArgumentException('CNPJ informado não encontrado.');
        }

        if (!empty($info['matriz'])) {
            $this->desativarAssociacoes($centro->id, $empresaId);
            $this->invalidarCache($empresaId);

            return;
        }

        $clienteFilialId = $this->resolverClienteFilialIdPorCnpj($campoCnpj, $empresaId);

        if (!$clienteFilialId) {
            throw new \InvalidArgumentException('Filial não encontrada para o CNPJ informado.');
        }

        $this->desativarAssociacoes($centro->id, $empresaId);

        $centroCustoFilial = CentroCustoFilial::where('empresa_id', $empresaId)
            ->where('centro_custo_id', $centro->id)
            ->where('cliente_filial_id', $clienteFilialId)
            ->first();

        if ($centroCustoFilial) {
            $centroCustoFilial->update(['ativo' => true]);
        } else {
            CentroCustoFilial::create([
                'empresa_id' => $empresaId,
                'centro_custo_id' => $centro->id,
                'cliente_filial_id' => $clienteFilialId,
                'ativo' => true,
            ]);
        }

        $this->invalidarCache($empresaId);
    }

    private function desativarAssociacoes(int $centroCustoId, int $empresaId): void
    {
        CentroCustoFilial::where('centro_custo_id', $centroCustoId)
            ->where('empresa_id', $empresaId)
            ->update(['ativo' => false]);
    }

    private function resolverClienteFilialIdPorCnpj(string $campoCnpj, int $empresaId): ?int
    {
        $cnpjDigits = preg_replace('/[^0-9]/', '', $campoCnpj);

        foreach ((new ClienteFilial())->getListaFilialAtiva($empresaId) as $filial) {
            $dados = (array) $filial->dados;
            $filialCnpj = preg_replace('/[^0-9]/', '', $dados['cnpj'] ?? '');

            if ($filialCnpj === $cnpjDigits) {
                return (int) $filial->id;
            }
        }

        return null;
    }

    private function invalidarCache(int $empresaId): void
    {
        (new CentroCusto())->forgetsCache($empresaId);
    }
}
