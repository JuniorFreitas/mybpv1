/**
 * Mentions @membro no TinyMCE via buscarMembros.
 * Insere <span class="wr-mention" data-user-id data-nome>@Nome</span>
 *
 * Cuidados: após contenteditable=false o TinyMCE injeta ZWSP; caret no meio
 * do parágrafo costuma cair em container elemento — normalizar antes de casar.
 */
const ZWSP_RE = /[\u200b\u200c\u200d\ufeff]/g
const MENTION_RE = /(?:^|[\s\u00a0\u200b\u200c\u200d\ufeff>(])@([^\s@]*)$/

export function attachWeeklyMentions(editor, options = {}) {
    const searchUrl = options.searchUrl || ''
    if (!editor || !searchUrl) {
        return () => {}
    }

    let menuEl = null
    let items = []
    let activeIndex = 0
    let debounceTimer = null
    let lastQuery = null
    let suppressBlurClose = false

    function destroyMenu() {
        if (debounceTimer) {
            clearTimeout(debounceTimer)
            debounceTimer = null
        }
        if (menuEl && menuEl.parentNode) {
            menuEl.parentNode.removeChild(menuEl)
        }
        menuEl = null
        items = []
        activeIndex = 0
        lastQuery = null
    }

    function escapeHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
    }

    function isTextNode(node) {
        return node && node.nodeType === 3
    }

    /**
     * Normaliza selection → { node: Text, offset } no ponto do caret.
     */
    function resolveTextCaret() {
        const rng = editor.selection.getRng()
        if (!rng || !rng.collapsed) return null

        let node = rng.startContainer
        let offset = rng.startOffset

        if (node.nodeType === 1) {
            const kids = node.childNodes
            // Caret entre filhos: preferir texto à direita (início), senão à esquerda (fim)
            const atOrAfter = kids[offset]
            const before = offset > 0 ? kids[offset - 1] : null

            if (isTextNode(atOrAfter)) {
                node = atOrAfter
                offset = 0
            } else if (isTextNode(before)) {
                node = before
                offset = before.data ? before.data.length : 0
            } else if (before && before.nodeType === 1) {
                // Logo após um elemento (ex.: span.wr-mention): texto seguinte ou fim
                if (isTextNode(atOrAfter)) {
                    node = atOrAfter
                    offset = 0
                } else {
                    // Sem text node vizinho — criar um ponto após o elemento não é possível aqui
                    return null
                }
            } else {
                return null
            }
        }

        if (!isTextNode(node)) return null
        const len = node.data ? node.data.length : 0
        offset = Math.max(0, Math.min(offset, len))
        return { node, offset }
    }

    /**
     * Localiza @query no text node atual (com suporte a ZWSP do TinyMCE).
     */
    function getMentionContext() {
        const caret = resolveTextCaret()
        if (!caret) return null

        const { node, offset } = caret
        const data = node.data || ''
        const before = data.slice(0, offset)
        // Normaliza ZWSP só para o match; índices reais vêm do lastIndexOf('@')
        const beforeNorm = before.replace(ZWSP_RE, '\u200b')
        const match = beforeNorm.match(MENTION_RE)
        if (!match) return null

        const query = match[1] || ''
        let atStart = before.lastIndexOf('@')
        if (atStart < 0) return null

        // Garante que entre @ e o caret só há a query (sem espaços)
        const between = before.slice(atStart + 1)
        if (/[\s\u00a0]/.test(between.replace(ZWSP_RE, ''))) return null

        return {
            node,
            query: between.replace(ZWSP_RE, ''),
            atStart,
            end: offset
        }
    }

    function positionMenu() {
        if (!menuEl) return
        const rng = editor.selection.getRng()
        if (!rng) return

        let rect
        try {
            const range = rng.cloneRange()
            range.collapse(true)
            const rects = range.getClientRects()
            rect = rects.length ? rects[rects.length - 1] : range.getBoundingClientRect()
        } catch (e) {
            return
        }
        if (!rect) return

        const iframe = editor.iframeElement
        const iframeRect = iframe ? iframe.getBoundingClientRect() : { top: 0, left: 0 }
        // Fallback: canto do iframe se o rect vier zerado (ZWSP / meio do texto)
        const absTop = rect.height || rect.width ? iframeRect.top + rect.bottom + 4 : iframeRect.top + 28
        const absLeft = rect.height || rect.width ? iframeRect.left + rect.left : iframeRect.left + 12
        menuEl.style.top = `${Math.max(8, absTop)}px`
        menuEl.style.left = `${Math.max(8, Math.min(absLeft, window.innerWidth - 220))}px`
    }

    function ensureMenu() {
        if (menuEl) return menuEl
        menuEl = document.createElement('div')
        menuEl.className = 'wr-mention-menu'
        menuEl.setAttribute('role', 'listbox')
        // Evita blur do editor ao clicar no menu (iframe)
        menuEl.addEventListener('mousedown', (ev) => {
            ev.preventDefault()
            suppressBlurClose = true
        })
        document.body.appendChild(menuEl)
        return menuEl
    }

    function renderMenu(emptyHint) {
        ensureMenu()
        if (emptyHint) {
            menuEl.innerHTML = `<div class="wr-mention-menu__empty">${escapeHtml(emptyHint)}</div>`
            positionMenu()
            return
        }
        if (!items.length) {
            menuEl.innerHTML = '<div class="wr-mention-menu__empty">Nenhum membro</div>'
            positionMenu()
            return
        }
        menuEl.innerHTML = items
            .map(
                (u, i) =>
                    `<button type="button" class="wr-mention-menu__item${i === activeIndex ? ' is-active' : ''}" data-idx="${i}" role="option">` +
                    `<span class="wr-mention-menu__avatar">${escapeHtml((u.nome || '?').charAt(0).toUpperCase())}</span>` +
                    `<span class="wr-mention-menu__label">${escapeHtml(u.nome || u.label || '')}</span>` +
                    `</button>`
            )
            .join('')
        menuEl.querySelectorAll('.wr-mention-menu__item').forEach((btn) => {
            btn.addEventListener('mousedown', (ev) => {
                ev.preventDefault()
                suppressBlurClose = true
                const idx = Number(btn.getAttribute('data-idx'))
                if (items[idx]) insertMention(items[idx])
            })
        })
        positionMenu()
    }

    function fetchMembers(query) {
        lastQuery = query
        if (debounceTimer) clearTimeout(debounceTimer)

        const q = (query || '').trim()
        if (!q) {
            items = []
            activeIndex = 0
            renderMenu('Digite o nome do membro')
            return
        }

        debounceTimer = setTimeout(() => {
            axios
                .get(searchUrl, { params: { busca: q, rows: 8 } })
                .then(({ data }) => {
                    if (lastQuery !== query) return
                    items = Array.isArray(data) ? data : []
                    activeIndex = 0
                    renderMenu()
                })
                .catch(() => {
                    if (lastQuery === query) destroyMenu()
                })
        }, 160)
    }

    function insertMention(user) {
        if (!user?.id) {
            destroyMenu()
            return
        }

        const ctx = getMentionContext()
        if (!ctx) {
            destroyMenu()
            return
        }

        const nome = user.nome || user.label || 'membro'
        // Espaço normal após a menção (evita &nbsp; “grudado” + caret estranho)
        const html =
            `<span class="wr-mention" contenteditable="false" data-user-id="${Number(user.id)}" data-nome="${escapeHtml(nome)}">@${escapeHtml(nome)}</span> `

        editor.undoManager.transact(() => {
            try {
                const doc = editor.getDoc()
                const del = doc.createRange()
                const len = ctx.node.data ? ctx.node.data.length : 0
                const start = Math.max(0, Math.min(ctx.atStart, len))
                const end = Math.max(start, Math.min(ctx.end, len))
                del.setStart(ctx.node, start)
                del.setEnd(ctx.node, end)
                editor.selection.setRng(del)
                editor.insertContent(html)
            } catch (e) {
                try {
                    editor.insertContent(html)
                } catch (err) {
                    /* ignore */
                }
            }
        })

        destroyMenu()
        suppressBlurClose = false

        // Colapsa após o conteúdo inserido. Evitar editor.focus() — em iframe
        // isso frequentemente joga o caret para o início do body.
        try {
            editor.selection.collapse(false)
        } catch (e) {
            /* ignore */
        }
        editor.nodeChanged()
        editor.fire('change')
        notifyMention(user)
    }

    function onKeyUp(e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            destroyMenu()
            return
        }
        if (['ArrowDown', 'ArrowUp', 'Enter'].includes(e.key) || [38, 40, 13].includes(e.keyCode)) {
            return
        }
        const ctx = getMentionContext()
        if (!ctx) {
            destroyMenu()
            return
        }
        fetchMembers(ctx.query)
    }

    function onKeyDown(e) {
        if (!menuEl) return
        if (e.key === 'Escape' || e.keyCode === 27) {
            e.preventDefault()
            e.stopPropagation()
            destroyMenu()
            return
        }
        if ((e.key === 'ArrowDown' || e.keyCode === 40) && items.length) {
            e.preventDefault()
            e.stopPropagation()
            activeIndex = (activeIndex + 1) % items.length
            renderMenu()
            return
        }
        if ((e.key === 'ArrowUp' || e.keyCode === 38) && items.length) {
            e.preventDefault()
            e.stopPropagation()
            activeIndex = (activeIndex - 1 + items.length) % items.length
            renderMenu()
            return
        }
        if ((e.key === 'Enter' || e.keyCode === 13) && items.length) {
            e.preventDefault()
            e.stopPropagation()
            insertMention(items[activeIndex])
        }
    }

    function notifyMention(user) {
        if (typeof options.onSelect === 'function') {
            try {
                options.onSelect(user)
            } catch (err) {
                /* ignore */
            }
        }
    }

    function onEditorClick() {
        // Reavalia: clique pode posicionar caret no meio do texto junto de um @
        const ctx = getMentionContext()
        if (!ctx) destroyMenu()
    }

    function onBlur() {
        if (suppressBlurClose) {
            suppressBlurClose = false
            return
        }
        // Delay: mousedown do menu acontece antes do blur
        setTimeout(() => {
            if (suppressBlurClose) {
                suppressBlurClose = false
                return
            }
            destroyMenu()
        }, 120)
    }

    editor.on('keyup', onKeyUp)
    editor.on('keydown', onKeyDown)
    editor.on('click', onEditorClick)
    editor.on('blur', onBlur)
    editor.on('Remove', destroyMenu)

    const win = editor.getWin()
    if (win) {
        win.addEventListener('scroll', destroyMenu, true)
    }

    return () => {
        editor.off('keyup', onKeyUp)
        editor.off('keydown', onKeyDown)
        editor.off('click', onEditorClick)
        editor.off('blur', onBlur)
        editor.off('Remove', destroyMenu)
        if (win) win.removeEventListener('scroll', destroyMenu, true)
        destroyMenu()
    }
}

export default { attachWeeklyMentions }
