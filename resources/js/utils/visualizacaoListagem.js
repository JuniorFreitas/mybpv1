export const CHAVE_VISUALIZACAO_LISTAGEM = 'mybp_visualizacao_listagem'
export const VISUALIZACAO_PADRAO = 'tabela'

const CHAVE_LEGADA_RECRUTAMENTO = 'mybp_recrutamento_visualizacao'

function normalizarVisualizacao(valor) {
    return valor === 'lista' || valor === 'tabela' ? valor : null
}

function lerMapa() {
    try {
        const bruto = localStorage.getItem(CHAVE_VISUALIZACAO_LISTAGEM)
        if (!bruto || bruto === 'lista' || bruto === 'tabela') {
            return {}
        }
        const mapa = JSON.parse(bruto)
        return mapa && typeof mapa === 'object' && !Array.isArray(mapa) ? mapa : {}
    } catch (e) {
        return {}
    }
}

/**
 * Lê a visualização da tela informada.
 * Sem escolha salva nessa tela, usa `padrao` (tabela no sistema; algumas telas passam lista).
 */
export function lerVisualizacaoListagem(tela, padrao = VISUALIZACAO_PADRAO) {
    const fallback = padrao === 'lista' ? 'lista' : VISUALIZACAO_PADRAO
    if (!tela) {
        return fallback
    }

    const mapa = lerMapa()
    const salva = normalizarVisualizacao(mapa[tela])
    if (salva) {
        return salva
    }

    if (tela === 'recrutamento') {
        try {
            const legada = normalizarVisualizacao(localStorage.getItem(CHAVE_LEGADA_RECRUTAMENTO))
            if (legada) {
                return gravarVisualizacaoListagem(tela, legada)
            }
        } catch (e) {
            return fallback
        }
    }

    return fallback
}

/**
 * Grava a visualização só da tela informada.
 */
export function gravarVisualizacaoListagem(tela, modo) {
    const valor = modo === 'lista' ? 'lista' : VISUALIZACAO_PADRAO
    if (!tela) {
        return valor
    }

    try {
        const mapa = lerMapa()
        mapa[tela] = valor
        localStorage.setItem(CHAVE_VISUALIZACAO_LISTAGEM, JSON.stringify(mapa))
    } catch (e) {
        return valor
    }

    return valor
}
