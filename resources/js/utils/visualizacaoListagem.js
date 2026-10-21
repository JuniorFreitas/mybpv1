export const CHAVE_VISUALIZACAO_LISTAGEM = 'mybp_visualizacao_listagem'
export const VISUALIZACAO_PADRAO = 'tabela'

const CHAVE_LEGADA_RECRUTAMENTO = 'mybp_recrutamento_visualizacao'

function normalizarVisualizacao(valor) {
    return valor === 'lista' || valor === 'tabela' ? valor : null
}

/**
 * Lê a visualização escolhida pelo usuário no sistema.
 * Sem escolha salva, o padrão é tabela.
 */
export function lerVisualizacaoListagem() {
    try {
        const atual = normalizarVisualizacao(localStorage.getItem(CHAVE_VISUALIZACAO_LISTAGEM))
        if (atual) {
            return atual
        }

        const legada = normalizarVisualizacao(localStorage.getItem(CHAVE_LEGADA_RECRUTAMENTO))
        if (legada) {
            localStorage.setItem(CHAVE_VISUALIZACAO_LISTAGEM, legada)
            return legada
        }
    } catch (e) {
        return VISUALIZACAO_PADRAO
    }

    return VISUALIZACAO_PADRAO
}

/**
 * Grava a visualização para todas as telas que usam o padrão.
 */
export function gravarVisualizacaoListagem(modo) {
    const valor = modo === 'lista' ? 'lista' : VISUALIZACAO_PADRAO

    try {
        localStorage.setItem(CHAVE_VISUALIZACAO_LISTAGEM, valor)
    } catch (e) {
        return valor
    }

    return valor
}
