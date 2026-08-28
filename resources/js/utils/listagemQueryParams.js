const DEBOUNCE_PADRAO_MS = 400

export const QUERY_PARAM_PAGE = 'page'
export const QUERY_PARAM_PAGES = 'pages'

export function valorValidoParaQuery(val) {
    return val !== '' && val !== null && val !== undefined
}

/**
 * Lê filtros da URL (?campoBusca=...&campoStatus=...) para controle.dados.
 */
export function lerFiltrosDaUrl(controleDados, campos = []) {
    if (!controleDados || !campos.length) {
        return
    }

    const urlParams = new URLSearchParams(window.location.search)

    campos.forEach((campo) => {
        if (urlParams.has(campo)) {
            controleDados[campo] = urlParams.get(campo)
        }
    })
}

/**
 * Lê paginação da URL. Retorna número da página inicial (>= 1).
 * Preenche controleDados.pages quando informado na query.
 */
export function lerPaginacaoDaUrl(controleDados, opcoes = {}) {
    const pagesDefault = normalizarPagesDefault(opcoes.pagesDefault)
    const urlParams = new URLSearchParams(window.location.search)

    if (urlParams.has(QUERY_PARAM_PAGES)) {
        const pages = parseInt(urlParams.get(QUERY_PARAM_PAGES), 10)
        if (!Number.isNaN(pages) && pages > 0) {
            controleDados.pages = pages
        }
    }

    if (!valorValidoParaQuery(controleDados.pages)) {
        controleDados.pages = pagesDefault
    }

    if (urlParams.has(QUERY_PARAM_PAGE)) {
        const page = parseInt(urlParams.get(QUERY_PARAM_PAGE), 10)
        if (!Number.isNaN(page) && page >= 1) {
            return page
        }
    }

    return 1
}

/**
 * Monta extras de paginação para sincronizar na URL.
 * page: só quando > 1 | pages: só quando diferente do padrão da tela.
 */
export function montarExtrasPaginacao(vm, opcoes = {}) {
    const pagesDefault = normalizarPagesDefault(opcoes.pagesDefault)
    const params = {}
    const atual = Number(vm.$refs?.componente?.atual ?? 1)
    const pages = Number(vm.controle?.dados?.pages ?? pagesDefault)

    if (atual > 1) {
        params[QUERY_PARAM_PAGE] = String(atual)
    }

    if (pages !== pagesDefault) {
        params[QUERY_PARAM_PAGES] = String(pages)
    }

    return params
}

export function aplicarPaginaInicialListagem(vm, page) {
    const pagina = Number(page)

    if (pagina > 1 && vm.$refs?.componente) {
        vm.$refs.componente.atual = pagina
    }
}

/**
 * Dispara busca da listagem. Por padrão reseta para página 1 (mudança de filtro).
 */
export function buscarListagem(vm, { resetPagina = true } = {}) {
    if (resetPagina && vm.$refs?.componente) {
        vm.$refs.componente.atual = 1
    }

    vm.$refs?.componente?.buscar?.()
}

/**
 * Persiste filtros + extras (paginação) na URL com history.replaceState.
 */
export function sincronizarFiltrosNaUrl(controleDados, campos = [], extras = {}) {
    const params = { ...extras }

    campos.forEach((campo) => {
        const val = controleDados[campo]
        if (valorValidoParaQuery(val)) {
            params[campo] = String(val)
        }
    })

    Object.keys(params).forEach((key) => {
        if (!valorValidoParaQuery(params[key])) {
            delete params[key]
        }
    })

    const qs = new URLSearchParams(params).toString()
    const url = window.location.pathname + (qs ? '?' + qs : '')

    window.history.replaceState(null, '', url)
}

/**
 * Watch deep em controle.dados com debounce para sincronizar URL.
 * opcoes.pagesDefault: inclui page/pages automaticamente no sync.
 */
export function criarWatchQueryParams(campos, opcoes = {}) {
    const debounceMs = opcoes.debounceMs ?? DEBOUNCE_PADRAO_MS
    const extras = opcoes.extras ?? (() => ({}))
    const incluirPaginacao = opcoes.pagesDefault != null

    return {
        handler() {
            if (this._syncUrlTimer) {
                clearTimeout(this._syncUrlTimer)
            }

            this._syncUrlTimer = setTimeout(() => {
                const extrasManual = typeof extras === 'function' ? extras.call(this) : extras
                const extrasPaginacao = incluirPaginacao ? montarExtrasPaginacao(this, opcoes) : {}
                sincronizarFiltrosNaUrl(this.controle.dados, campos, {
                    ...extrasManual,
                    ...extrasPaginacao
                })
            }, debounceMs)
        },
        deep: true
    }
}

/**
 * Bootstrap: lê URL e dispara busca inicial no próximo tick.
 */
export function inicializarFiltrosComQueryParams(vm, campos, opcoes = {}) {
    lerFiltrosDaUrl(vm.controle?.dados, campos)

    const paginaInicial = lerPaginacaoDaUrl(vm.controle?.dados, opcoes)

    vm.$nextTick(() => {
        aplicarPaginaInicialListagem(vm, paginaInicial)

        const buscar = opcoes.buscar ?? (() => buscarListagem(vm, { resetPagina: false }))

        if (typeof buscar === 'function') {
            buscar.call(vm)
        }
    })
}

function normalizarPagesDefault(pagesDefault) {
    const valor = Number(pagesDefault ?? 20)

    return Number.isNaN(valor) || valor <= 0 ? 20 : valor
}

export { DEBOUNCE_PADRAO_MS }
