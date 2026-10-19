/**
 * Escopo Global vs Empresa para cadastro de Carteira/Etiqueta (admin 100).
 */
export default {
    data() {
        return {
            empresasLista: [],
            formEscopo: 'global',
            formEmpresaId: '',
        }
    },
    computed: {
        filtroEscopoOpcoes() {
            return [
                { value: '', label: 'Todos os escopos' },
                { value: 'global', label: 'Apenas globais' },
                { value: 'empresa', label: 'Apenas por empresa' },
            ]
        },
        filtroEmpresaOpcoes() {
            return [
                { value: '', label: 'Todas as empresas' },
                ...this.empresasLista.map((e) => ({
                    value: String(e.id),
                    label: this.labelEmpresa(e),
                    meta: e.cnpj || '',
                    raw: e,
                })),
            ]
        },
        formEscopoOpcoes() {
            return [
                { value: 'global', label: 'Global (todas as empresas)' },
                { value: 'empresa', label: 'Empresa específica (customizado)' },
            ]
        },
        formEmpresaOpcoes() {
            return this.empresasLista.map((e) => ({
                value: String(e.id),
                label: this.labelEmpresa(e),
                meta: e.cnpj || '',
                raw: e,
            }))
        },
    },
    methods: {
        labelEmpresa(e) {
            if (!e) return '—'
            const nome = e.nome_fantasia || e.razao_social || e.apelido || `Empresa #${e.id}`
            return `#${e.id} — ${nome}`
        },
        labelEscopoItem(item) {
            if (!item || item.empresa_id == null) return 'Global'
            const emp = item.empresa
            if (emp) return this.labelEmpresa(emp)
            return `Empresa #${item.empresa_id}`
        },
        carregarEmpresasEscopo() {
            if (!this.isAdminGlobal) return Promise.resolve()
            return axios.get(`${URL_ADMIN}/cadastro/carteirastreinamento/empresas`)
                .then((res) => {
                    this.empresasLista = res.data || []
                })
                .catch(() => {
                    this.empresasLista = []
                })
        },
        resetFormEscopo() {
            this.formEscopo = this.isAdminGlobal ? 'global' : 'empresa'
            this.formEmpresaId = ''
        },
        aplicarEscopoNoForm(item) {
            if (!item || item.empresa_id == null) {
                this.formEscopo = 'global'
                this.formEmpresaId = ''
                return
            }
            this.formEscopo = 'empresa'
            this.formEmpresaId = String(item.empresa_id)
        },
        payloadEscopoStore() {
            if (!this.isAdminGlobal) {
                return { global: 0 }
            }
            if (this.formEscopo === 'global') {
                return { global: 1 }
            }
            return {
                global: 0,
                empresa_id: this.formEmpresaId ? Number(this.formEmpresaId) : null,
            }
        },
        validarEscopoStore() {
            if (!this.isAdminGlobal) return true
            if (this.formEscopo === 'empresa' && !this.formEmpresaId) {
                if (typeof mostraErro === 'function') {
                    mostraErro('', 'Selecione a empresa do template customizado')
                }
                return false
            }
            return true
        },
    },
}
