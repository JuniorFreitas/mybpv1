<?php

namespace App\Contracts\IntegracaoSpa;

use App\Data\IntegracaoSpa\VagaSpaDetailData;
use App\Data\IntegracaoSpa\VagaSpaListItem;
use App\Data\IntegracaoSpa\VagasAbertasPagina;

interface VagaIntegracaoSpaQuery
{
    /**
     * @return list<VagaSpaListItem>
     */
    public function listarPreviewAtivasPorEmpresaId(int $empresaId, int $limite): array;

    public function paginarAtivasPorEmpresaId(int $empresaId, int $porPagina, int $page): VagasAbertasPagina;

    public function buscarAtivaPorEmpresaApelidoIdESlug(string $apelido, int $vagaAbertaId, string $slug): ?VagaSpaDetailData;
}
