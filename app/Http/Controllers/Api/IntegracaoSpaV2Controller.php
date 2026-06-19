<?php

namespace App\Http\Controllers\Api;

use App\Contracts\IntegracaoSpa\EmpresaIntegracaoSpaQuery;
use App\Contracts\IntegracaoSpa\VagaIntegracaoSpaQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IntegracaoSpaV2Controller extends Controller
{
    public function __construct(
        private readonly EmpresaIntegracaoSpaQuery $empresas,
        private readonly VagaIntegracaoSpaQuery $vagas,
    ) {
    }

    public function empresasAtivas(): JsonResponse
    {
        $lista = $this->empresas->listarEmpresasAtivas();

        return response()->json([
            'success' => true,
            'dados' => array_map(static fn ($dto) => $dto->toArray(), $lista),
        ]);
    }

    public function empresaComPreview(string $apelido): JsonResponse
    {
        $empresa = $this->empresas->buscarEmpresaAtivaPorApelido($apelido);

        if ($empresa === null) {
            return response()->json([
                'success' => false,
                'msg' => 'Empresa não encontrada',
            ], 404);
        }

        $preview = $this->vagas->listarPreviewAtivasPorEmpresaId($empresa->id, 6);

        return response()->json([
            'success' => true,
            'dados' => [
                'empresa' => $empresa->toArray(),
                'vagas_abertas' => array_map(static fn ($item) => $item->toArray(), $preview),
            ],
        ]);
    }

    public function vagasAbertasPaginadas(Request $request, string $apelido): JsonResponse
    {
        $empresa = $this->empresas->buscarEmpresaAtivaPorApelido($apelido);

        if ($empresa === null) {
            return response()->json([
                'success' => false,
                'msg' => 'Empresa não encontrada',
            ], 404);
        }

        $page = max(1, (int) $request->query('page', 1));
        $porPagina = max(1, (int) config('integracao_spa.vagas_abertas_por_pagina', 50));
        $pagina = $this->vagas->paginarAtivasPorEmpresaId($empresa->id, $porPagina, $page);

        return response()->json([
            'success' => true,
            'dados' => [
                'empresa' => $empresa->toArray(),
                'vagas_abertas' => $pagina->toArray(),
            ],
        ]);
    }

    public function vagaAbertaDetalhe(string $apelido, int $vaga_aberta_id, string $slug_vaga_aberta): JsonResponse
    {
        $empresa = $this->empresas->buscarEmpresaAtivaPorApelido($apelido);

        if ($empresa === null) {
            return response()->json([
                'success' => false,
                'msg' => 'Empresa não encontrada',
            ], 404);
        }

        $vaga = $this->vagas->buscarAtivaPorEmpresaApelidoIdESlug($apelido, $vaga_aberta_id, $slug_vaga_aberta);

        if ($vaga === null) {
            return response()->json([
                'success' => false,
                'msg' => 'Vaga não encontrada',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'dados' => [
                'empresa' => $empresa->toArray(),
                'vaga_aberta' => $vaga->toArray(),
            ],
        ]);
    }
}
