<?php

namespace App\Services\IntegracaoSpa;

use App\Contracts\IntegracaoSpa\EmpresaIntegracaoSpaQuery;
use App\Data\IntegracaoSpa\EmpresaSpaData;
use App\Models\Cliente;
use App\Support\IntegracaoSpa\IntegracaoSpaEmpresasAtivasCache;

class EmpresaIntegracaoSpaEloquent implements EmpresaIntegracaoSpaQuery
{
    public function listarEmpresasAtivas(): array
    {
        return IntegracaoSpaEmpresasAtivasCache::remember(function (): array {
            $clientes = Cliente::withoutGlobalScopes()
                ->select([
                    'id',
                    'razao_social',
                    'apelido',
                    'logradouro',
                    'numero',
                    'complemento',
                    'bairro',
                    'municipio',
                    'uf',
                    'cep',
                    'missao',
                    'visao',
                    'valores',
                    'ativo',
                ])
                ->where('ativo', true)
                ->whereNotNull('apelido')
                ->where('apelido', '!=', '')
                ->orderBy('razao_social')
                ->with(['Logo'])
                ->get();

            return $clientes->map(static fn (Cliente $c) => EmpresaSpaData::fromCliente($c))->all();
        });
    }

    public function buscarEmpresaAtivaPorApelido(string $apelido): ?EmpresaSpaData
    {
        $cliente = Cliente::withoutGlobalScopes()
            ->select([
                'id',
                'razao_social',
                'apelido',
                'logradouro',
                'numero',
                'complemento',
                'bairro',
                'municipio',
                'uf',
                'cep',
                'missao',
                'visao',
                'valores',
                'ativo',
            ])
            ->where('ativo', true)
            ->where('apelido', $apelido)
            ->with(['Logo'])
            ->first();

        if ($cliente === null) {
            return null;
        }

        return EmpresaSpaData::fromCliente($cliente);
    }
}
