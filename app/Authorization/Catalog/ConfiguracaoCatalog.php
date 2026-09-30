<?php

namespace App\Authorization\Catalog;

use App\Authorization\HabilidadeDefinition;

final class ConfiguracaoCatalog
{
    /**
     * @return list<HabilidadeDefinition>
     */
    public static function definitions(): array
    {
        $modulo = 'configuracao';
        $moduloLabel = 'Configurações';

        return [
            new HabilidadeDefinition(
                nome: 'configuracao_habilidades',
                modulo: $modulo,
                recurso: 'habilidades',
                acao: 'access',
                descricao: 'Acessar rota/menu habilidades',
                moduloLabel: $moduloLabel,
                recursoLabel: 'Habilidades',
                acaoLabel: 'Acessar',
            ),
            new HabilidadeDefinition(
                nome: 'configuracao_habilidades_insert',
                modulo: $modulo,
                recurso: 'habilidades',
                acao: 'insert',
                descricao: 'Pode inserir uma nova habilidade',
                moduloLabel: $moduloLabel,
                recursoLabel: 'Habilidades',
                acaoLabel: 'Inserir',
            ),
            new HabilidadeDefinition(
                nome: 'configuracao_habilidades_update',
                modulo: $modulo,
                recurso: 'habilidades',
                acao: 'update',
                descricao: 'Pode alterar uma habilidade',
                moduloLabel: $moduloLabel,
                recursoLabel: 'Habilidades',
                acaoLabel: 'Alterar',
            ),
            new HabilidadeDefinition(
                nome: 'configuracao_habilidades_delete',
                modulo: $modulo,
                recurso: 'habilidades',
                acao: 'delete',
                descricao: 'Pode apagar uma habilidade',
                moduloLabel: $moduloLabel,
                recursoLabel: 'Habilidades',
                acaoLabel: 'Apagar',
            ),
            new HabilidadeDefinition(
                nome: 'configuracao_papel',
                modulo: $modulo,
                recurso: 'papel',
                acao: 'access',
                descricao: 'Acessar rota/menu papeis',
                moduloLabel: $moduloLabel,
                recursoLabel: 'Grupos de usuários',
                acaoLabel: 'Acessar',
            ),
            new HabilidadeDefinition(
                nome: 'configuracao_papel_insert',
                modulo: $modulo,
                recurso: 'papel',
                acao: 'insert',
                descricao: 'Pode cadastrar um papel',
                moduloLabel: $moduloLabel,
                recursoLabel: 'Grupos de usuários',
                acaoLabel: 'Inserir',
            ),
            new HabilidadeDefinition(
                nome: 'configuracao_papel_update',
                modulo: $modulo,
                recurso: 'papel',
                acao: 'update',
                descricao: 'Pode alterar um papel',
                moduloLabel: $moduloLabel,
                recursoLabel: 'Grupos de usuários',
                acaoLabel: 'Alterar',
            ),
            new HabilidadeDefinition(
                nome: 'configuracao_papel_delete',
                modulo: $modulo,
                recurso: 'papel',
                acao: 'delete',
                descricao: 'Pode apagar um papel',
                moduloLabel: $moduloLabel,
                recursoLabel: 'Grupos de usuários',
                acaoLabel: 'Apagar',
            ),
        ];
    }
}
