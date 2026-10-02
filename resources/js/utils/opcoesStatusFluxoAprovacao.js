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

/** Aprovação intermediária não é status atual: o card mostra a etapa seguinte. */
export const STATUS_APROVACAO_INTERMEDIARIA = ['aprovado_gestor', 'aprovado_extra']

export function opcoesStatusFluxoAtual(opts = {}) {
    return buildOpcoesStatusFluxoAprovacao(opts).filter((opcao) => !STATUS_APROVACAO_INTERMEDIARIA.includes(opcao.value))
}

export function normalizarStatusUrlFluxo(status) {
    if (STATUS_APROVACAO_INTERMEDIARIA.includes(status)) return ''
    return status || ''
}

/**
 * Etapa visível do fluxo gestor → extra → RH.
 * @param {object|null} item
 * @param {{ campoGestor?: string, campoExtra?: string, campoRh?: string, temAprovacaoExtra?: boolean }} [opts]
 */
export function etapaAtualFluxoAprovacao(item, opts = {}) {
    const campoGestor = opts.campoGestor || 'status_aprovacao_gestor'
    const campoExtra = opts.campoExtra || 'status_aprovacao_extra'
    const campoRh = opts.campoRh || 'status_aprovacao_rh'
    const temExtra = !!opts.temAprovacaoExtra
    if (!item) return 'pendente_gestor'
    if (item[campoGestor] === 'reprovado') return 'reprovado_gestor'
    if (item[campoExtra] === 'reprovado') return 'reprovado_extra'
    if (item[campoRh] === 'reprovado') return 'reprovado_rh'
    if (item[campoRh] === 'aprovado') return 'aprovado_rh'
    if (item[campoGestor] !== 'aprovado') return 'pendente_gestor'
    if (temExtra && item[campoExtra] !== 'aprovado') return 'pendente_extra'
    return 'pendente_rh'
}

export function varianteStatusFluxo(etapa) {
    if (String(etapa).indexOf('reprovado') === 0) return 'reprovado'
    if (etapa === 'aprovado_rh') return 'rh'
    return 'pendente'
}

export function textoStatusFluxo(etapa, nomeExtra) {
    const extra = nomeExtra || 'Extra'
    const textos = {
        reprovado_gestor: 'Reprovado Gestor',
        reprovado_extra: `Reprovado ${extra}`,
        reprovado_rh: 'Reprovado RH',
        aprovado_rh: 'Aprovado RH',
        pendente_gestor: 'Pendente Gestor',
        pendente_extra: `Pendente ${extra}`,
        pendente_rh: 'Pendente RH'
    }
    return textos[etapa] || 'Pendente Gestor'
}
