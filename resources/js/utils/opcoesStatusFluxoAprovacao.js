/**
 * Opções de filtro de status por etapa do fluxo (gestor → extra → RH).
 * Mesmo padrão da Transferência, sem etapas origem/destino/único.
 *
 * @param {{ temAprovacaoExtra?: boolean, nomeAprovacaoExtra?: string }} [opts]
 * @returns {{ value: string, label: string }[]}
 */
export function buildOpcoesStatusFluxoAprovacao(opts = {}) {
    const temAprovacaoExtra = !!opts.temAprovacaoExtra
    const nomeExtra = opts.nomeAprovacaoExtra || 'Extra'

    const opcoes = [
        { value: '', label: 'Todos os status' },
        { value: 'aberto', label: 'Em aberto (qualquer etapa)' },
        { value: 'pendente_gestor', label: 'Pendente Gestor' },
        { value: 'aprovado_gestor', label: 'Aprovado Gestor' },
        { value: 'reprovado_gestor', label: 'Reprovado Gestor' }
    ]

    if (temAprovacaoExtra) {
        opcoes.push(
            { value: 'pendente_extra', label: `Pendente ${nomeExtra}` },
            { value: 'aprovado_extra', label: `Aprovado ${nomeExtra}` },
            { value: 'reprovado_extra', label: `Reprovado ${nomeExtra}` }
        )
    }

    opcoes.push(
        { value: 'pendente_rh', label: 'Pendente RH' },
        { value: 'aprovado_rh', label: 'Aprovado RH' },
        { value: 'reprovado_rh', label: 'Reprovado RH' },
        { value: 'reprovado', label: 'Reprovado (qualquer etapa)' }
    )

    return opcoes
}
