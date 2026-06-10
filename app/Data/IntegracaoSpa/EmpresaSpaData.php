<?php

namespace App\Data\IntegracaoSpa;

use App\Models\Cliente;

final class EmpresaSpaData
{
    public function __construct(
        public readonly int $id,
        public readonly string $razao_social,
        public readonly ?string $apelido,
        public readonly ?string $missao,
        public readonly ?string $visao,
        public readonly ?string $valores,
        public readonly array $endereco,
        public readonly ?string $endereco_completo,
        public readonly ?LogoSpaData $logotipo,
    ) {
    }

    /**
     * Reidrata a partir do array serializado no cache da listagem SPA.
     */
    public static function fromSpaCacheArray(array $data): self
    {
        $endereco = $data['endereco'] ?? [];
        if (! is_array($endereco)) {
            $endereco = [];
        }

        return new self(
            id: (int) ($data['id'] ?? 0),
            razao_social: (string) ($data['razao_social'] ?? ''),
            apelido: isset($data['apelido']) && is_string($data['apelido']) ? $data['apelido'] : null,
            missao: isset($data['missao']) && is_string($data['missao']) ? $data['missao'] : null,
            visao: isset($data['visao']) && is_string($data['visao']) ? $data['visao'] : null,
            valores: isset($data['valores']) && is_string($data['valores']) ? $data['valores'] : null,
            endereco: [
                'logradouro' => $endereco['logradouro'] ?? null,
                'numero' => $endereco['numero'] ?? null,
                'complemento' => $endereco['complemento'] ?? null,
                'bairro' => $endereco['bairro'] ?? null,
                'municipio' => $endereco['municipio'] ?? null,
                'uf' => $endereco['uf'] ?? null,
                'cep' => $endereco['cep'] ?? null,
            ],
            endereco_completo: isset($data['endereco_completo']) && is_string($data['endereco_completo'])
                ? $data['endereco_completo']
                : null,
            logotipo: LogoSpaData::fromSpaCacheArray(
                isset($data['logotipo']) && is_array($data['logotipo']) ? $data['logotipo'] : null
            ),
        );
    }

    public static function fromCliente(Cliente $cliente): self
    {
        $primeiroLogo = $cliente->relationLoaded('Logo') ? $cliente->Logo->first() : null;

        return new self(
            id: (int) $cliente->id,
            razao_social: (string) ($cliente->razao_social ?? ''),
            apelido: $cliente->apelido,
            missao: $cliente->missao,
            visao: $cliente->visao,
            valores: $cliente->valores,
            endereco: [
                'logradouro' => $cliente->logradouro,
                'numero' => $cliente->numero,
                'complemento' => $cliente->complemento,
                'bairro' => $cliente->bairro,
                'municipio' => $cliente->municipio,
                'uf' => $cliente->uf,
                'cep' => $cliente->cep,
            ],
            endereco_completo: $cliente->endereco_completo ?? null,
            logotipo: LogoSpaData::fromArquivo($primeiroLogo, $cliente->apelido),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'razao_social' => $this->razao_social,
            'apelido' => $this->apelido,
            'missao' => $this->missao,
            'visao' => $this->visao,
            'valores' => $this->valores,
            'endereco' => $this->endereco,
            'endereco_completo' => $this->endereco_completo,
            'logotipo' => $this->logotipo?->toArray(),
        ];
    }
}
