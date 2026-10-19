/**
 * Resolução de rótulo de Lotação (nome fantasia + CNPJ) a partir de lista_ccs.
 * Fonte de verdade: grupo do centro de custo em lista_ccs.centros_custos
 * (mesmo critério da listagem/processo) — evita cair na matriz quando o CC é de filial.
 */

export function formatLotacao(nomeFantasia, razaoSocial, cnpj) {
    const nome = String(nomeFantasia || razaoSocial || '').trim()
    const doc = String(cnpj || '').trim()
    if (!nome && !doc) return ''
    if (!nome) return doc
    if (!doc) return nome
    return `${nome} - ${doc}`
}

/**
 * Localiza a chave CNPJ (grupo) do CC em lista_ccs.
 * @param {object|null} listaCcs
 * @param {string|number|null|undefined} centroCustoId
 * @param {string|number|null|undefined} centroCustoFilialId
 * @returns {string} chave do grupo ou ''
 */
export function resolverCnpjKeyViaListaCcs(listaCcs, centroCustoId, centroCustoFilialId) {
    if (!listaCcs || !listaCcs.centros_custos) return ''

    const ccId = centroCustoId != null && centroCustoId !== '' ? String(centroCustoId) : ''
    const ccfId =
        centroCustoFilialId != null && centroCustoFilialId !== '' ? String(centroCustoFilialId) : ''
    let cnpjKey = ''

    // 1) Match pelo id do centro de custo (cobre matriz e filial)
    if (ccId) {
        Object.keys(listaCcs.centros_custos).some((key) => {
            const lista = listaCcs.centros_custos[key] || []
            const encontrado = lista.find((item) => String(item.id) === ccId)
            if (encontrado) {
                cnpjKey = key
                return true
            }
            return false
        })
    }

    // 2) Fallback pelo vínculo filial_id
    if (!cnpjKey && ccfId) {
        Object.keys(listaCcs.centros_custos).some((key) => {
            const lista = listaCcs.centros_custos[key] || []
            const encontrado = lista.find((item) => String(item.filial_id) === ccfId)
            if (encontrado) {
                cnpjKey = key
                return true
            }
            return false
        })
    }

    return cnpjKey
}

/**
 * @param {object|null} listaCcs
 * @param {string|number|null|undefined} centroCustoId
 * @param {string|number|null|undefined} centroCustoFilialId
 * @returns {string}
 */
export function resolverLabelLotacaoViaListaCcs(listaCcs, centroCustoId, centroCustoFilialId) {
    if (!listaCcs || !listaCcs.cnpjs) return ''
    const cnpjKey = resolverCnpjKeyViaListaCcs(listaCcs, centroCustoId, centroCustoFilialId)
    if (!cnpjKey || !listaCcs.cnpjs[cnpjKey]) return ''
    const info = listaCcs.cnpjs[cnpjKey]
    return formatLotacao(info.nome_fantasia, info.razao_social, info.cnpj)
}

/**
 * Resolve label de lotação para o formulário (colaborador selecionado).
 * Ordem: vínculo filial carregado → lista_ccs pelo CC → matriz auth.
 *
 * @param {object} opts
 * @param {string|number|null|undefined} opts.centroCustoId
 * @param {boolean|number|string|null|undefined} opts.filial
 * @param {string|number|null|undefined} opts.centroCustoFilialId
 * @param {Array} [opts.centroCustoSelecionado] filiais do CC (estrutura do endpoint centro-custos)
 * @param {object|null} [opts.listaCcs]
 * @param {object|null} [opts.authconfiguracao]
 * @returns {string}
 */
export function resolverLabelLotacaoAtual(opts = {}) {
    const {
        centroCustoId,
        filial,
        centroCustoFilialId,
        centroCustoSelecionado = [],
        listaCcs = null,
        authconfiguracao = null
    } = opts

    if (
        [undefined, null, ''].includes(centroCustoId) &&
        [undefined, null, ''].includes(centroCustoFilialId)
    ) {
        return ''
    }

    const ehFilial = filial === true || filial === 1 || filial === '1'
    const filiaisAuth = (authconfiguracao && authconfiguracao.cnpjs && authconfiguracao.cnpjs.filiais) || []

    if (ehFilial && centroCustoFilialId) {
        const ccfId = String(centroCustoFilialId)
        const vinculo = (centroCustoSelecionado || []).find((v) => String(v.id) === ccfId)
        const filialRel = vinculo && (vinculo.filial || vinculo.Filial)
        if (filialRel) {
            const viaRel = formatLotacao(filialRel.nome_fantasia, filialRel.razao_social, filialRel.cnpj)
            if (viaRel && String(filialRel.cnpj || '').trim()) return viaRel

            const clienteFilialId = vinculo.cliente_filial_id
            if (clienteFilialId != null && clienteFilialId !== '') {
                const cf = filiaisAuth.find((f) => String(f.id) === String(clienteFilialId))
                if (cf) {
                    const viaAuth = formatLotacao(cf.nome_fantasia || cf.nome_fntasia, cf.razao_social, cf.cnpj)
                    if (viaAuth) return viaAuth
                }
            }
            if (viaRel) return viaRel
        }

        if (vinculo && vinculo.cliente_filial_id != null && vinculo.cliente_filial_id !== '') {
            const cf = filiaisAuth.find((f) => String(f.id) === String(vinculo.cliente_filial_id))
            if (cf) {
                const viaAuth = formatLotacao(cf.nome_fantasia || cf.nome_fntasia, cf.razao_social, cf.cnpj)
                if (viaAuth) return viaAuth
            }
        }
    }

    const viaLista = resolverLabelLotacaoViaListaCcs(listaCcs, centroCustoId, centroCustoFilialId)
    if (viaLista) return viaLista

    const matrizAuth = authconfiguracao && authconfiguracao.cnpjs && authconfiguracao.cnpjs.matriz
    if (matrizAuth) {
        const viaMatriz = formatLotacao(matrizAuth.nome_fantasia, matrizAuth.razao_social, matrizAuth.cnpj)
        if (viaMatriz) return viaMatriz
    }

    return ''
}

/**
 * Normaliza flags de CC/filial vindas da admissão/autocomplete.
 * Mantém centro_custo_filial_id quando existir mesmo se a flag filial vier inconsistente.
 */
export function normalizarCcFilialDaAdmissao(fonte = {}) {
    const centro_custo_id = fonte.centro_custo_id || ''
    let filial = fonte.filial === true || fonte.filial === 1 || fonte.filial === '1'
    const centro_custo_filial_id = fonte.centro_custo_filial_id || null
    if (!filial && centro_custo_filial_id) {
        filial = true
    }
    return { centro_custo_id, filial, centro_custo_filial_id }
}
