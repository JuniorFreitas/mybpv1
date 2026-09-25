/**
 * Query params do Weekly Report (histórico + F5).
 * Ex.: /g/weekly-report?quadro=12&lista=3&tarefa=45
 */

export const QP_QUADRO = 'quadro'
export const QP_LISTA = 'lista'
export const QP_TAREFA = 'tarefa'

function parseId(val) {
    if (val === null || val === undefined || val === '') return null
    const n = parseInt(String(val), 10)
    return Number.isFinite(n) && n > 0 ? n : null
}

export function lerWeeklyQueryParams() {
    const params = new URLSearchParams(window.location.search)
    return {
        quadro: parseId(params.get(QP_QUADRO)),
        lista: parseId(params.get(QP_LISTA)),
        tarefa: parseId(params.get(QP_TAREFA))
    }
}

/**
 * Atualiza a URL preservando outros query params alheios.
 * @param {{ quadro?: number|null, lista?: number|null, tarefa?: number|null }} state
 * @param {{ replace?: boolean }} opts
 */
export function escreverWeeklyQueryParams(state = {}, { replace = false } = {}) {
    const params = new URLSearchParams(window.location.search)
    ;[QP_QUADRO, QP_LISTA, QP_TAREFA].forEach((k) => params.delete(k))

    const quadro = parseId(state.quadro)
    const lista = parseId(state.lista)
    const tarefa = parseId(state.tarefa)

    if (quadro) params.set(QP_QUADRO, String(quadro))
    // lista/tarefa só fazem sentido com quadro; tarefa exige lista
    if (quadro && lista) params.set(QP_LISTA, String(lista))
    if (quadro && lista && tarefa) params.set(QP_TAREFA, String(tarefa))

    const qs = params.toString()
    const url = window.location.pathname + (qs ? `?${qs}` : '') + (window.location.hash || '')
    const histState = { weeklyReport: true, quadro, lista, tarefa }

    if (replace) {
        window.history.replaceState(histState, '', url)
    } else {
        window.history.pushState(histState, '', url)
    }
}

export default {
    QP_QUADRO,
    QP_LISTA,
    QP_TAREFA,
    lerWeeklyQueryParams,
    escreverWeeklyQueryParams
}
