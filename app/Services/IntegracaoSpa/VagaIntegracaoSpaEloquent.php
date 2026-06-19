<?php

namespace App\Services\IntegracaoSpa;

use App\Contracts\IntegracaoSpa\VagaIntegracaoSpaQuery;
use App\Data\IntegracaoSpa\VagaSpaDetailData;
use App\Data\IntegracaoSpa\VagaSpaListItem;
use App\Data\IntegracaoSpa\VagasAbertasPagina;
use App\Models\Cliente;
use App\Models\VagasAbertas;
use App\Support\IntegracaoSpa\IntegracaoSpaVagasAbertasPaginaCache;
use App\Support\IntegracaoSpa\VagaSpaSlug;

class VagaIntegracaoSpaEloquent implements VagaIntegracaoSpaQuery
{
    public function listarPreviewAtivasPorEmpresaId(int $empresaId, int $limite): array
    {
        $vagas = $this->baseQueryAtivasPorEmpresa($empresaId)
            ->orderByDesc('updated_at')
            ->limit($limite)
            ->get();

        return $vagas->map(static fn (VagasAbertas $v) => VagaSpaListItem::fromVagasAbertas($v))->all();
    }

    public function paginarAtivasPorEmpresaId(int $empresaId, int $porPagina, int $page): VagasAbertasPagina
    {
        return IntegracaoSpaVagasAbertasPaginaCache::remember($empresaId, $page, $porPagina, function () use ($empresaId, $porPagina, $page): VagasAbertasPagina {
            $paginator = $this->baseQueryAtivasPorEmpresa($empresaId)
                ->orderByDesc('updated_at')
                ->paginate(perPage: $porPagina, columns: ['*'], pageName: 'page', page: $page);

            $itens = collect($paginator->items())->map(static fn (VagasAbertas $v) => VagaSpaListItem::fromVagasAbertas($v))->all();

            return new VagasAbertasPagina(
                itens: $itens,
                currentPage: $paginator->currentPage(),
                lastPage: $paginator->lastPage(),
                perPage: $paginator->perPage(),
                total: (int) $paginator->total(),
            );
        });
    }

    public function buscarAtivaPorEmpresaApelidoIdESlug(string $apelido, int $vagaAbertaId, string $slug): ?VagaSpaDetailData
    {
        $empresa = Cliente::withoutGlobalScopes()
            ->where('ativo', true)
            ->where('apelido', $apelido)
            ->first(['id']);

        if ($empresa === null) {
            return null;
        }

        $vaga = $this->baseQueryAtivasPorEmpresa((int) $empresa->id)
            ->where('id', $vagaAbertaId)
            ->first();

        if ($vaga === null) {
            return null;
        }

        if (VagaSpaSlug::fromTitulo($vaga->titulo) !== $slug) {
            return null;
        }

        return VagaSpaDetailData::fromVagasAbertas($vaga);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<VagasAbertas>
     */
    private function baseQueryAtivasPorEmpresa(int $empresaId)
    {
        return VagasAbertas::withoutGlobalScopes()
            ->select([
                'id',
                'vaga_id',
                'titulo',
                'descricao',
                'municipio_id',
                'empresa_id',
                'ativo',
                'ativo_sistema',
                'created_at',
                'updated_at',
            ])
            ->where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->with([
                'Municipio:id,nome,uf',
                'Cargo:id,nome',
            ]);
    }
}
