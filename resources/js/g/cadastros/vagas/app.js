import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import FiltroListagem from '../../../components/ui/FiltroListagem'
import ComboboxAutoComplete from '../../../components/ComboboxAutoComplete'
import { lerFiltrosDaUrl, lerPaginacaoDaUrl, sincronizarFiltrosNaUrl, criarWatchQueryParams, montarExtrasPaginacao, aplicarPaginaInicialListagem, buscarListagem } from '../../../utils/listagemQueryParams'

const CAMPOS_FILTRO_URL = ['campoBusca', 'campoStatus']
const PAGES_DEFAULT = 100

const app = createApp({
    components: {
        ComboboxAutoComplete
    },
    data() {
        return {
            tituloJanela: 'Cadastrando Cargo',
            preloadAjax: false,
            editando: false,
            apagado: false,

            pages: 10,

            form: {
                nome: '',
                ativo: true,
                cbo_id: null,
                cbo_codigo: '',
                codigo_familia: '',
                cbo_titulo: '',
                cbo_familia: '',
                cbo_descricao_sumaria: '',
                autocomplete_label_cbo: '',
                vencimentos: [],
                treinamentos_globais_count: 0
            },

            formDefault: null,
            campoNome: null,
            hash: `vaga_${parseInt(Math.random() * 999999)}`,

            cadastrado: false,
            atualizado: false,

            lista: [],

            urlTreinamentoIndustria: `${URL_ADMIN}/cadastro/treinamentoindustria`,

            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoStatus: '',
                    pages: PAGES_DEFAULT
                }
            },

            dropdownAbertoKey: null
        }
    },
    computed: {
        filtroStatusOpcoes() {
            return [
                { value: '', label: 'Todos os status' },
                { value: 'true', label: 'Apenas ativos' },
                { value: 'false', label: 'Apenas inativos' }
            ]
        }
    },
    mounted() {
        this.urlParamGetFiltros()
        const paginaInicial = lerPaginacaoDaUrl(this.controle.dados, { pagesDefault: PAGES_DEFAULT })
        this.formDefault = _.cloneDeep(this.form) //copia
        this.$nextTick(() => {
            aplicarPaginaInicialListagem(this, paginaInicial)
            buscarListagem(this, { resetPagina: false })
        })
        document.addEventListener('click', this.onClickOutside)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },
    watch: {
        'controle.dados': criarWatchQueryParams(CAMPOS_FILTRO_URL, { pagesDefault: PAGES_DEFAULT })
    },
    methods: {
        urlParamGetFiltros() {
            lerFiltrosDaUrl(this.controle.dados, CAMPOS_FILTRO_URL)
        },
        syncUrlFiltros() {
            sincronizarFiltrosNaUrl(
                this.controle.dados,
                CAMPOS_FILTRO_URL,
                montarExtrasPaginacao(this, { pagesDefault: PAGES_DEFAULT })
            )
        },
        onSubmitFiltro() {
            this.atualizar()
        },
        onSelectFiltro() {
            this.atualizar()
        },
        fecharOutrosComboboxes(manter) {
            const combos = [['filtro-status', 'comboFiltroStatus']]

            combos.forEach(([id, refName]) => {
                if (id !== manter && this.$refs[refName]?.close) {
                    this.$refs[refName].close()
                }
            })
        },
        onClickOutside(event) {
            if (event?.target?.closest?.('.dropdown')) return
            if (event?.target?.closest?.('.mybp-combobox-wrap')) return
            this.dropdownAbertoKey = null
            this.fecharOutrosComboboxes(null)
        },
        toggleDropdown(vagaId) {
            if (!vagaId) return
            const key = `vaga:${vagaId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(vagaId) {
            return this.dropdownAbertoKey === `vaga:${vagaId}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        abrirEdicaoVaga(vagaId) {
            this.fecharDropdown()
            this.formAlterar(vagaId)
            this.$refs.janelaCadastrar?.abrirModal()
        },
        normalizarVencimentosResposta(data) {
            const lista = data?.vencimentos || data?.Vencimentos || []

            return lista.map((item) => ({
                id: item.id,
                label: item.label,
                segmento_nome:
                    item.segmento_treinamento?.nome ||
                    item.SegmentoTreinamento?.nome ||
                    item.segmento_nome ||
                    'Geral'
            }))
        },
        resumoTreinamentos(vaga) {
            const vinculados = Number(vaga?.treinamentos_vinculados_count ?? 0)
            const globais = Number(vaga?.treinamentos_globais_count ?? 0)
            const total = Number(vaga?.treinamentos_total_count ?? vinculados + globais)

            if (total === 0) {
                return 'Nenhum'
            }

            if (vinculados === 0 && globais > 0) {
                return globais === 1
                    ? '1 treinamento (todos os cargos)'
                    : `${globais} treinamentos (todos os cargos)`
            }

            if (globais === 0) {
                return vinculados === 1 ? '1 treinamento' : `${vinculados} treinamentos`
            }

            const labelTotal = total === 1 ? '1 treinamento' : `${total} treinamentos`
            const labelVinculados = vinculados === 1 ? '1 vinculado' : `${vinculados} vinculados`
            const labelGlobais = globais === 1 ? '1 global' : `${globais} globais`

            return `${labelTotal} (${labelVinculados}, ${labelGlobais})`
        },
        resumoTreinamentosForm() {
            const vinculados = this.form?.vencimentos?.length ?? 0
            const globais = Number(this.form?.treinamentos_globais_count ?? 0)

            return this.resumoTreinamentos({
                treinamentos_vinculados_count: vinculados,
                treinamentos_globais_count: globais,
                treinamentos_total_count: vinculados + globais
            })
        },
        temCbo(vaga) {
            return !!(vaga?.cbo_vinculado || vaga?.cbo_codigo || vaga?.cbo_titulo)
        },
        tratarErroSalvar(error, acao) {
            this.preloadAjax = false
            const erros = error?.response?.data?.erros
            const msg = error?.response?.data?.msg || `Erro ao ${acao} cargo.`

            if (erros && typeof erros === 'object') {
                const detalhes = Object.values(erros)
                    .flat()
                    .filter(Boolean)
                    .join(' ')
                mostraErro('', detalhes || msg)
                return
            }

            mostraErro('', msg)
        },
        caminhoAutocompleteCbos() {
            return 'autocomplete/cbos-ativos'
        },
        selecionaCbo(obj) {
            this.form.cbo_id = obj.id || null
            this.form.cbo_codigo = obj.codigo || ''
            this.form.codigo_familia = obj.codigo_familia || ''
            this.form.cbo_titulo = obj.titulo || ''
            this.form.cbo_familia = obj.familia || ''
            this.form.cbo_descricao_sumaria = obj.descricao_sumaria || ''
            this.form.autocomplete_label_cbo = obj.label || ''
        },
        validarCboSelecionado() {
            if (!this.form.autocomplete_label_cbo) {
                this.form.cbo_id = null
                this.form.cbo_codigo = ''
                this.form.codigo_familia = ''
                this.form.cbo_titulo = ''
                this.form.cbo_familia = ''
                this.form.cbo_descricao_sumaria = ''
                return
            }

            if (!this.form.cbo_id) {
                this.form.autocomplete_label_cbo = ''
                this.form.cbo_codigo = ''
                this.form.codigo_familia = ''
                this.form.cbo_titulo = ''
                this.form.cbo_familia = ''
                this.form.cbo_descricao_sumaria = ''
            }
        },

        formNovo() {
            this.cadastrado = false
            this.atualizado = false
            this.editando = false

            this.tituloJanela = 'Cadastrando Cargo'

            formReset()
            setupCampo()

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = false
        },
        cadastrar() {
            formReset()

            $('#janelaCadastrar :input:enabled').trigger('blur')

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.preloadAjax = true
            axios
                .post(`${URL_ADMIN}/cadastro/vagas`, this.form)
                .then((response) => {
                    if (response.status === 201) {
                        this.preloadAjax = false
                        this.cadastrado = true
                        this.atualizar()
                    }
                })
                .catch((error) => this.tratarErroSalvar(error, 'cadastrar'))
        },
        formAlterar(id) {
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Alterando Cargo'
            this.preloadAjax = true
            formReset()

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = true

            axios
                .get(`${URL_ADMIN}/cadastro/vagas/${id}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.form.autocomplete_label_cbo = response.data.autocomplete_label_cbo || ''
                    this.form.vencimentos = this.normalizarVencimentosResposta(response.data)
                    this.form.treinamentos_globais_count = response.data.treinamentos_globais_count || 0
                    this.editando = true
                    this.preloadAjax = false
                    setupCampo()
                })
                .catch((error) => this.tratarErroSalvar(error, 'carregar'))
        },

        alterar() {
            formReset()
            $('#janelaCadastrar :input:enabled').trigger('blur')

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            this.form._method = 'PUT'
            this.preloadAjax = true

            axios
                .put(`${URL_ADMIN}/cadastro/vagas/${this.form.id}`, this.form)
                .then((response) => {
                    this.preloadAjax = false
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => this.tratarErroSalvar(error, 'alterar'))
        },

        carregou(dados) {
            this.lista = dados
            this.controle.carregando = false
            this.$nextTick(() => this.syncUrlFiltros())
        },

        carregando() {
            this.controle.carregando = true
        },

        atualizar() {
            buscarListagem(this, { resetPagina: true })
        }
    }
})

registerGlobals(app)
app.component('FiltroListagem', FiltroListagem)
app.mount('#app')
