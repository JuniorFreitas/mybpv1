<?php

namespace App\Authorization\Catalog;

use App\Authorization\HabilidadeDefinition;

final class AdmissaoCihCatalog
{
    /**
     * @return list<HabilidadeDefinition>
     */
    public static function definitions(): array
    {
        $modulo = 'admissao';
        $moduloLabel = 'Admissão';
        $recurso = 'cih';
        $recursoLabel = 'CIH';

        return [
            new HabilidadeDefinition(
                nome: 'admissao_cih',
                modulo: $modulo,
                recurso: $recurso,
                acao: 'access',
                descricao: 'Acessar menu Apontamento - CIH',
                moduloLabel: $moduloLabel,
                recursoLabel: $recursoLabel,
                acaoLabel: 'Acessar',
            ),
            new HabilidadeDefinition(
                nome: 'admissao_cih_lancar',
                modulo: $modulo,
                recurso: $recurso,
                acao: 'lancar',
                descricao: 'Pode lançar uma ocorrencia CIH',
                moduloLabel: $moduloLabel,
                recursoLabel: $recursoLabel,
                acaoLabel: 'Lançar',
            ),
            new HabilidadeDefinition(
                nome: 'admissao_cih_aprovar',
                modulo: $modulo,
                recurso: $recurso,
                acao: 'aprovar',
                descricao: 'Pode aprovar uma ocorrencia CIH',
                moduloLabel: $moduloLabel,
                recursoLabel: $recursoLabel,
                acaoLabel: 'Aprovar',
            ),
            new HabilidadeDefinition(
                nome: 'admissao_cih_ver_todas',
                modulo: $modulo,
                recurso: $recurso,
                acao: 'ver_todas',
                descricao: 'Pode visualizar todas as CIH (sem ampliar aprovação)',
                moduloLabel: $moduloLabel,
                recursoLabel: $recursoLabel,
                acaoLabel: 'Ver todas',
            ),
            new HabilidadeDefinition(
                nome: 'admissao_cih_privilegio_adm',
                modulo: $modulo,
                recurso: $recurso,
                acao: 'privilegio_adm',
                descricao: 'Pode visualizar todas as CIH e aprovar como administrador',
                moduloLabel: $moduloLabel,
                recursoLabel: $recursoLabel,
                acaoLabel: 'Privilégio administrador',
            ),
        ];
    }
}
