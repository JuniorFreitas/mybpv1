<?php

namespace App\Contracts\IntegracaoSpa;

use App\Data\IntegracaoSpa\EmpresaSpaData;

interface EmpresaIntegracaoSpaQuery
{
    /**
     * @return list<EmpresaSpaData>
     */
    public function listarEmpresasAtivas(): array;

    public function buscarEmpresaAtivaPorApelido(string $apelido): ?EmpresaSpaData;
}
