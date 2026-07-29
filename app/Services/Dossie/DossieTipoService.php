<?php

namespace App\Services\Dossie;

use App\Models\DossieTipo;
use Illuminate\Support\Collection;

class DossieTipoService
{
    /**
     * Seções para o frontend (upload + flags de modelo/assinatura).
     *
     * @return array<int, array<string, mixed>>
     */
    public function listarSecoes(?int $empresaId = null): array
    {
        $empresaId = $this->resolverEmpresaId($empresaId);

        return $this->ativos($empresaId)->map(function (DossieTipo $tipo) {
            return [
                'tipo' => $tipo->tipo,
                'chave' => $tipo->chave,
                'label' => $tipo->label,
                'tipo_modelo' => $tipo->tipo_modelo,
                'tipo_documento' => $tipo->tipo_documento,
                'tem_modelo' => (bool) $tipo->tem_modelo,
                'permite_assinatura' => (bool) $tipo->permite_assinatura,
                'ordem' => (int) $tipo->ordem,
                'empresa_id' => $tipo->empresa_id,
                'escopo' => $tipo->empresa_id ? 'empresa' : 'global',
            ];
        })->values()->all();
    }

    /**
     * Relacionamentos no formato legado do controller (comum / del).
     *
     * @return array<int, array{comum: string, del: string}>
     */
    public function listarRelacionamentosFormatados(?int $empresaId = null): array
    {
        $empresaId = $this->resolverEmpresaId($empresaId);

        return $this->ativos($empresaId)->map(function (DossieTipo $tipo) {
            return [
                'comum' => $tipo->chave,
                'del' => $tipo->chave . 'Del',
            ];
        })->values()->all();
    }

    /**
     * Chaves snake_case dos tipos ativos (campos do form).
     *
     * @return array<int, string>
     */
    public function listarChaves(?int $empresaId = null): array
    {
        return $this->ativos($this->resolverEmpresaId($empresaId))->pluck('chave')->all();
    }

    /**
     * Nomes StudlyCase para eager-load (getDocumentoRelacionado{Tipo}).
     *
     * @return array<int, string>
     */
    public function listarRelacionamentosEager(?int $empresaId = null): array
    {
        return $this->ativos($this->resolverEmpresaId($empresaId))
            ->map(fn (DossieTipo $tipo) => 'getDocumentoRelacionado' . $tipo->tipo)
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public function tiposModeloAssinatura(?int $empresaId = null): array
    {
        return DossieTipo::tiposModeloAssinatura($this->resolverEmpresaId($empresaId));
    }

    /**
     * @return array<int, string>
     */
    public function tiposDocumentoAssinatura(?int $empresaId = null): array
    {
        return $this->ativos($this->resolverEmpresaId($empresaId))
            ->filter(fn (DossieTipo $tipo) => $tipo->permite_assinatura && $tipo->tipo_documento)
            ->pluck('tipo_documento')
            ->unique()
            ->values()
            ->all();
    }

    public function tipoModeloParaTipoDocumento(string $tipoModelo, ?int $empresaId = null): string
    {
        $map = DossieTipo::mapTipoModeloParaDocumento($this->resolverEmpresaId($empresaId));

        return $map[$tipoModelo] ?? $tipoModelo;
    }

    public function tipoDocumentoParaTipoModelo(string $tipoDocumento, ?int $empresaId = null): string
    {
        $map = array_flip(DossieTipo::mapTipoModeloParaDocumento($this->resolverEmpresaId($empresaId)));

        return $map[$tipoDocumento] ?? $tipoDocumento;
    }

    public function permiteAssinatura(string $tipoModelo, ?int $empresaId = null): bool
    {
        return in_array($tipoModelo, $this->tiposModeloAssinatura($empresaId), true);
    }

    public function labelPorTipoModelo(string $tipoModelo, ?int $empresaId = null): ?string
    {
        $item = $this->ativos($this->resolverEmpresaId($empresaId))
            ->first(fn (DossieTipo $tipo) => $tipo->tipo_modelo === $tipoModelo);

        return $item?->label;
    }

    public function ativos(?int $empresaId = null): Collection
    {
        return DossieTipo::ativosOrdenados($this->resolverEmpresaId($empresaId));
    }

    protected function resolverEmpresaId(?int $empresaId = null): ?int
    {
        if ($empresaId !== null) {
            return $empresaId;
        }

        $authEmpresaId = auth()->user()->empresa_id ?? null;

        return $authEmpresaId !== null ? (int) $authEmpresaId : null;
    }
}
