<?php

namespace App\Authorization\Catalog;

use App\Authorization\HabilidadeDefinition;

final class PrivilegioCatalog
{
    /**
     * @return list<HabilidadeDefinition>
     */
    public static function definitions(): array
    {
        $modulo = 'privilegio';
        $moduloLabel = 'Privilégios';

        return [
            new HabilidadeDefinition(
                nome: 'privilegio_aprovar_por_gestor',
                modulo: $modulo,
                recurso: 'aprovacao',
                acao: 'gestor',
                descricao: 'Aprovação de Gestor',
                moduloLabel: $moduloLabel,
                recursoLabel: 'Aprovação',
                acaoLabel: 'Como gestor',
            ),
            new HabilidadeDefinition(
                nome: 'privilegio_aprovar_por_rh',
                modulo: $modulo,
                recurso: 'aprovacao',
                acao: 'rh',
                descricao: 'Aprovação de Rh',
                moduloLabel: $moduloLabel,
                recursoLabel: 'Aprovação',
                acaoLabel: 'Como RH',
            ),
            new HabilidadeDefinition(
                nome: 'privilegio_realizar-lancamento',
                modulo: $modulo,
                recurso: 'lancamento',
                acao: 'realizar',
                descricao: 'Definir lançamento como realizado ou não',
                moduloLabel: $moduloLabel,
                recursoLabel: 'Lançamento',
                acaoLabel: 'Realizar',
            ),
            new HabilidadeDefinition(
                nome: 'privilegio_processo_demitir',
                modulo: $modulo,
                recurso: 'processo',
                acao: 'demitir',
                descricao: 'Pode demitir um funcionário, diretamente no menu processo',
                moduloLabel: $moduloLabel,
                recursoLabel: 'Processo',
                acaoLabel: 'Demitir',
            ),
        ];
    }
}
