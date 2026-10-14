/**
 * Converte o subconjunto de Markdown usado pelo Assistente do Manual
 * (negrito, itálico, listas e parágrafos) em HTML.
 *
 * Todo o texto é escapado antes de qualquer marcação, então a saída só contém
 * as tags geradas aqui e pode ser usada em v-html.
 */

const ESCAPES = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }

function escapar(texto) {
    return String(texto).replace(/[&<>"']/g, (caractere) => ESCAPES[caractere])
}

function inline(texto) {
    return escapar(texto)
        .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
        .replace(/(^|[\s(])\*(?!\s)([^*]+?)\*(?=[\s).,;:!?]|$)/g, '$1<em>$2</em>')
}

const ITEM_ORDENADO = /^\s*(\d+)[.)]\s+(.*)$/
const ITEM_SIMPLES = /^\s*[-*•]\s+(.*)$/

export function markdownSeguro(texto) {
    const blocos = []
    let lista = null

    const fecharLista = () => {
        if (lista) {
            // "start" preserva a numeração quando um parágrafo interrompe a lista.
            const inicio = lista.tag === 'ol' && lista.inicio > 1 ? ` start="${lista.inicio}"` : ''
            blocos.push(`<${lista.tag}${inicio}>${lista.itens.map((item) => `<li>${item}</li>`).join('')}</${lista.tag}>`)
            lista = null
        }
    }

    String(texto || '').split(/\r?\n/).forEach((linha) => {
        // Linha recuada logo após um item (texto ou subitem) continua o item, sem quebrar a numeração.
        if (lista && /^\s{2,}\S/.test(linha)) {
            const subitem = linha.match(ITEM_SIMPLES)
            lista.itens[lista.itens.length - 1] += '<br>' + (subitem ? '• ' + inline(subitem[1]) : inline(linha.trim()))
            return
        }

        const ordenado = linha.match(ITEM_ORDENADO)
        const simples = ordenado ? null : linha.match(ITEM_SIMPLES)
        const tag = ordenado ? 'ol' : simples ? 'ul' : null

        if (tag) {
            if (!lista || lista.tag !== tag) {
                fecharLista()
                lista = { tag, itens: [], inicio: ordenado ? Number(ordenado[1]) : 1 }
            }
            lista.itens.push(inline(ordenado ? ordenado[2] : simples[1]))
            return
        }

        if (linha.trim() === '') {
            // Linha em branco entre itens numerados não quebra a lista.
            return
        }

        fecharLista()
        blocos.push(`<p>${inline(linha.trim())}</p>`)
    })

    fecharLista()

    return blocos.join('')
}
