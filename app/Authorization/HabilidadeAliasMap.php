<?php

namespace App\Authorization;

/**
 * Mapa alias → nome canônico (Gate). Não remove habilidades do banco.
 *
 * Use para nomes legados/typos que devem resolver para a habilidade real.
 */
final class HabilidadeAliasMap
{
    /**
     * @return array<string, string> alias => canônico
     */
    public static function map(): array
    {
        return [
            // Pós-admissão: variações de nome do menu/access
            'pos_admissao' => 'admissao_pos_admissao',
            'admissao_posadmissao' => 'admissao_pos_admissao',
            'posadmissao' => 'admissao_pos_admissao',

            // Lote 2: família posadmissao_* → admissao_pos_*
            'posadmissao_form_rh' => 'admissao_pos_form_rh',
            'posadmissao_form_adm' => 'admissao_pos_form_adm',
            'posadmissao_form_ssma' => 'admissao_pos_form_ssma',
            'posadmissao_avaliar' => 'admissao_pos_avaliar',
            'posadmissao_avaliar_insert' => 'admissao_pos_avaliar_insert',
            'posadmissao_avaliar_update' => 'admissao_pos_avaliar_update',
            'posadmissao_desmobilizar' => 'admissao_pos_desmobilizar',
            'posadmissao_desmobilizar_insert' => 'admissao_pos_desmobilizar_insert',
            'posadmissao_desmobilizar_update' => 'admissao_pos_desmobilizar_update',
            'posadmissao_entrevista_desligamento' => 'admissao_pos_entrevista_desligamento',
            'posadmissao_entrevista_desligamento_insert' => 'admissao_pos_entrevista_desligamento_insert',
            'posadmissao_entrevista_desligamento_update' => 'admissao_pos_entrevista_desligamento_update',

            // Dossiê no histórico: prefixo antigo vs aba
            'admissao_historico_dossie_insert' => 'historico_dossie_insert',
            'admissao_historico_dossie_update' => 'historico_dossie_update',

            // Avaliação 90 dias
            'admissao_avaliacao_noventa_insert' => 'avaliacao_noventa_insert',
        ];
    }
}
