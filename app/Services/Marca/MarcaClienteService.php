<?php

namespace App\Services\Marca;

use App\Models\Cliente;

/**
 * Identidade do cliente (nome + logotipo do cadastro) para telas públicas co-branded.
 */
class MarcaClienteService
{
    /**
     * @return array{nome: string, logo_url: ?string}
     */
    public function dados(?Cliente $cliente): array
    {
        if (!$cliente) {
            return ['nome' => '', 'logo_url' => null];
        }

        $logo = $cliente->Logo()->orderByDesc('arquivos.id')->first();

        return [
            'nome' => (string) ($cliente->nome_fantasia ?: $cliente->razao_social ?: ''),
            // Imagem original: a miniatura fica sem definição no cabeçalho.
            'logo_url' => $logo?->url ?: null,
        ];
    }
}
