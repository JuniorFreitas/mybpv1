<?php

namespace App\Authorization\Cloud;

/**
 * Catálogo tipado das capacidades Cloud (espelha habilidade_clouds / UI Cloud.vue).
 */
final class CloudCapabilityCatalog
{
    public const DOWNLOAD = 'Download';
    public const VISUALIZAR = 'Visualizar';
    public const DETALHES = 'Detalhes';
    public const EDITAR = 'Editar';
    public const MOVER = 'Mover';
    public const DELETAR = 'Deletar';
    public const ATUALIZAR = 'Atualizar';
    public const REVISAR = 'Revisar';
    public const APROVAR = 'Aprovar';

    /**
     * @return list<array{nome: string, slug: string, label: string}>
     */
    public static function all(): array
    {
        return [
            ['nome' => self::DOWNLOAD, 'slug' => 'download', 'label' => 'Download'],
            ['nome' => self::VISUALIZAR, 'slug' => 'visualizar', 'label' => 'Visualizar'],
            ['nome' => self::DETALHES, 'slug' => 'detalhes', 'label' => 'Detalhes'],
            ['nome' => self::EDITAR, 'slug' => 'editar', 'label' => 'Editar'],
            ['nome' => self::MOVER, 'slug' => 'mover', 'label' => 'Mover'],
            ['nome' => self::DELETAR, 'slug' => 'deletar', 'label' => 'Deletar'],
            ['nome' => self::ATUALIZAR, 'slug' => 'atualizar', 'label' => 'Atualizar'],
            ['nome' => self::REVISAR, 'slug' => 'revisar', 'label' => 'Revisar'],
            ['nome' => self::APROVAR, 'slug' => 'aprovar', 'label' => 'Aprovar'],
        ];
    }

    /**
     * @return list<string>
     */
    public static function nomes(): array
    {
        return array_column(self::all(), 'nome');
    }

    public static function isValidNome(string $nome): bool
    {
        return in_array($nome, self::nomes(), true);
    }
}
