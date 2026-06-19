<?php

namespace App\Data\IntegracaoSpa;

use App\Models\VagasAbertas;
use App\Support\IntegracaoSpa\VagaSpaSlug;
use Carbon\Carbon;
use Carbon\CarbonInterface;

final class VagaSpaListItem
{
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly ?string $titulo,
        public readonly ?string $descricao,
        public readonly ?array $municipio,
        public readonly ?array $cargo,
        public readonly ?string $publicado_em,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromSpaCacheArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? 0),
            slug: (string) ($data['slug'] ?? ''),
            titulo: isset($data['titulo']) ? (is_string($data['titulo']) ? $data['titulo'] : null) : null,
            descricao: isset($data['descricao']) ? (is_string($data['descricao']) ? $data['descricao'] : null) : null,
            municipio: isset($data['municipio']) && is_array($data['municipio']) ? $data['municipio'] : null,
            cargo: isset($data['cargo']) && is_array($data['cargo']) ? $data['cargo'] : null,
            publicado_em: isset($data['publicado_em']) && is_string($data['publicado_em']) ? $data['publicado_em'] : null,
        );
    }

    public static function fromVagasAbertas(VagasAbertas $vaga): self
    {
        $municipio = $vaga->relationLoaded('Municipio') && $vaga->Municipio
            ? [
                'id' => (int) $vaga->Municipio->id,
                'nome' => $vaga->Municipio->nome,
                'uf' => $vaga->Municipio->uf,
            ]
            : null;

        $cargoModel = $vaga->relationLoaded('Cargo') ? $vaga->Cargo : ($vaga->relationLoaded('VagaSelecionada') ? $vaga->VagaSelecionada : null);
        $cargo = $cargoModel
            ? [
                'id' => (int) $cargoModel->id,
                'nome' => $cargoModel->nome,
            ]
            : null;

        $created = $vaga->getRawOriginal('created_at') ?? $vaga->getAttributes()['created_at'] ?? null;
        $publicadoEm = null;
        if ($created !== null && $created !== '') {
            $publicadoEm = $created instanceof CarbonInterface
                ? $created->toIso8601String()
                : Carbon::parse((string) $created)->toIso8601String();
        }

        return new self(
            id: (int) $vaga->id,
            slug: VagaSpaSlug::fromTitulo($vaga->titulo),
            titulo: $vaga->titulo,
            descricao: $vaga->descricao,
            municipio: $municipio,
            cargo: $cargo,
            publicado_em: $publicadoEm,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'titulo' => $this->titulo,
            'descricao' => $this->descricao,
            'municipio' => $this->municipio,
            'cargo' => $this->cargo,
            'publicado_em' => $this->publicado_em,
        ];
    }
}
