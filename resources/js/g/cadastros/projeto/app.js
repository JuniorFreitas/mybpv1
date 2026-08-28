import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import ComboboxAutoComplete from '../../../components/ComboboxAutoComplete'
import FiltroListagem from '../../../components/ui/FiltroListagem'
import { lerFiltrosDaUrl, lerPaginacaoDaUrl, sincronizarFiltrosNaUrl, criarWatchQueryParams, montarExtrasPaginacao, aplicarPaginaInicialListagem, buscarListagem } from '../../../utils/listagemQueryParams'

const CAMPOS_FILTRO_URL = ['campoBusca', 'campoDisponibilidade', 'campoVinculoVagas']
const PAGES_DEFAULT = 100

const app = createApp({
    components: {
        ComboboxAutoComplete
    },
    data() {
        return {
            tituloJanela: 'Cadastrando Projeto',
            preloadAjax: false,
            editando: false,
            apagado: false,

            pages: 10,

            form: {
                nome: '',
                qnt_total: 1,
                preenchidas: 0,
                vagas_projeto: [],
                vagas_projetoDelete: [],
                autocomplete_label_vaga_aberta: ''
            },

            hash: `mastertag_${parseInt(Math.random() * 999999)}`,

            formDefault: null,
            campoNome: null,

            cadastrado: false,
            atualizado: false,

            lista: [],

            controle: {
                carregando: false,
                dados: {
                    campoBusca: '',
                    campoDisponibilidade: '',
                    campoVinculoVagas: '',
                    pages: PAGES_DEFAULT
                }
            },

            dropdownAbertoKey: null
        }
    },
    computed: {
        totalRestanteVagas() {
            const totalProjeto = Number(this.form.qnt_total || 0)
            const totalVagas = this.totalAlocadoVagas
            const somatorio = totalProjeto - totalVagas

            return Number.isNaN(somatorio) ? 0 : somatorio
        },
        totalAlocadoVagas() {
            return (this.form.vagas_projeto || []).reduce((total, vaga) => {
                return total + Number(vaga?.qnt_total || 0)
            }, 0)
        },
        quantidadeMinimaProjetoTotal() {
            return Math.max(1, this.totalAlocadoVagas)
        },
        filtroDisponibilidadeOpcoes() {
            return [
                { value: '', label: 'Todos' },
                { value: 'com_restante', label: 'Com vagas restantes' },
                { value: 'esgotado', label: 'Esgotado' }
            ]
        },
        filtroVinculoVagasOpcoes() {
            return [
                { value: '', label: 'Todas' },
                { value: 'com_vinculo', label: 'Com vagas abertas vinculadas' },
                { value: 'sem_vinculo', label: 'Sem vagas abertas vinculadas' }
            ]
        }
    },
    mounted() {
        this.urlParamGetFiltros()
        const paginaInicial = lerPaginacaoDaUrl(this.controle.dados, { pagesDefault: PAGES_DEFAULT })
        this.formDefault = _.cloneDeep(this.form)
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
            const combos = [
                ['filtro-disponibilidade', 'comboFiltroDisponibilidade'],
                ['filtro-vinculo-vagas', 'comboFiltroVinculoVagas']
            ]

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
        toggleDropdown(projetoId) {
            if (!projetoId) return
            const key = `projeto:${projetoId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(projetoId) {
            return this.dropdownAbertoKey === `projeto:${projetoId}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        abrirEdicaoProjeto(projetoId) {
            this.fecharDropdown()
            this.formAlterar(projetoId).then((carregou) => {
                if (carregou) {
                    this.$refs.janelaCadastrar?.abrirModal()
                }
            })
        },
        resumoDisponibilidade(projeto) {
            if (projeto?.tem_vagas_restantes) {
                const restantes = Number(projeto?.qnt_total_restante ?? 0)
                return restantes === 1 ? '1 vaga restante' : `${restantes} vagas restantes`
            }

            return 'Sem vagas restantes'
        },
        resumoVagasVinculadas(projeto) {
            const total = Number(projeto?.vagas_projeto_count ?? 0)

            if (total === 0) {
                return 'Nenhuma vaga aberta vinculada'
            }

            return total === 1 ? '1 vaga aberta vinculada' : `${total} vagas abertas vinculadas`
        },
        resumoPreenchimento(projeto) {
            const preenchidas = Number(projeto?.preenchidas ?? 0)
            const total = Number(projeto?.qnt_total ?? 0)
            const percentual = Number(projeto?.percentual_preenchido ?? 0)

            if (total === 0) {
                return 'Não informado'
            }

            return `${preenchidas} de ${total} preenchidas (${percentual}%)`
        },
        tituloVagaProjeto(item) {
            return item?.vaga_aberta?.titulo || 'Vaga não informada'
        },
        cargoVagaProjeto(item) {
            return item?.vaga_aberta?.vaga?.nome
                || item?.vaga_aberta?.Vaga?.nome
                || 'Cargo não informado'
        },
        localVagaProjeto(item) {
            const vagaAberta = item?.vaga_aberta
            if (!vagaAberta) {
                return 'Local não informado'
            }

            if (vagaAberta.municipio_label) {
                return vagaAberta.municipio_label
            }

            const municipio = vagaAberta.municipio || vagaAberta.Municipio
            if (municipio?.nome && municipio?.uf) {
                return `${municipio.nome} - ${municipio.uf}`
            }

            return 'Local não informado'
        },
        exibirCargoVagaProjeto(item) {
            const titulo = (this.tituloVagaProjeto(item) || '').trim().toLowerCase()
            const cargo = (this.cargoVagaProjeto(item) || '').trim().toLowerCase()

            return cargo !== '' && cargo !== titulo
        },
        percentualReservaVinculo(item) {
            const reservadas = Number(item?.qnt_total || 0)
            const maximo = Number(this.quantidadeMaximaVagaLinha(item) || 0)

            if (maximo <= 0) {
                return 0
            }

            return Math.min(100, Math.round((reservadas / maximo) * 100))
        },
        normalizarVagaAberta(obj) {
            if (!obj) {
                return null
            }

            const municipio = obj.municipio || obj.Municipio
            const cargo = obj.vaga || obj.Vaga

            return {
                id: obj.id,
                titulo: obj.titulo,
                empresa_id: obj.empresa_id,
                municipio_id: obj.municipio_id ?? municipio?.id ?? null,
                municipio_label: obj.municipio_label || (municipio?.nome && municipio?.uf
                    ? `${municipio.nome} - ${municipio.uf}`
                    : null),
                municipio: municipio ? {
                    id: municipio.id,
                    nome: municipio.nome,
                    uf: municipio.uf
                } : null,
                vaga: cargo ? {
                    id: cargo.id,
                    nome: cargo.nome
                } : null
            }
        },
        vagaLinhaExistente(item) {
            return !!item?.id && !item?.novo
        },
        hintVagaLinha(item) {
            const preenchidas = Number(item?.qnt_preenchida || 0)

            if (preenchidas > 0) {
                const label = preenchidas === 1
                    ? '1 vaga já contratada'
                    : `${preenchidas} vagas já contratadas`

                return `${label} neste vínculo. A reserva mínima é ${preenchidas}.`
            }

            return ''
        },
        hintVagaLinhaTitulo() {
            return 'Restrição por vagas ocupadas'
        },
        totalDistribuidoProjeto(projeto) {
            const capacidade = Number(projeto?.qnt_total || 0)
            const disponivel = Number(projeto?.qnt_total_restante || 0)

            return Math.max(capacidade - disponivel, 0)
        },
        percentualOcupacaoProjeto(projeto) {
            const total = Number(projeto?.qnt_total || 0)

            if (total <= 0) {
                return 0
            }

            return Math.min(100, Math.round((Number(projeto?.preenchidas || 0) / total) * 100))
        },
        classeStatusProjeto(projeto) {
            return projeto?.tem_vagas_restantes
                ? 'mybp-projeto-status mybp-projeto-status--ativo'
                : 'mybp-projeto-status mybp-projeto-status--esgotado'
        },
        quantidadeMinimaVagaLinha(item) {
            const preenchidas = Number(item?.qnt_preenchida || 0)
            return Math.max(1, preenchidas)
        },
        quantidadeMaximaVagaLinha(item) {
            const atual = Number(item?.qnt_total || 0)
            const restante = Number(this.totalRestanteVagas || 0)
            return Math.max(this.quantidadeMinimaVagaLinha(item), atual + restante)
        },
        podeRemoverVagaProjeto(item) {
            return Number(item?.qnt_preenchida || 0) === 0
        },
        ajustarQuantidadeVaga(index) {
            const item = this.form.vagas_projeto[index]
            if (!item) {
                return
            }

            const minimo = this.quantidadeMinimaVagaLinha(item)
            const maximo = this.quantidadeMaximaVagaLinha(item)
            let valor = Number(item.qnt_total || 0)

            if (Number.isNaN(valor) || valor < minimo) {
                valor = minimo
            }

            if (valor > maximo) {
                valor = maximo
            }

            item.qnt_total = valor
        },
        removerVagaProjeto(index) {
            const item = this.form.vagas_projeto[index]
            if (!item) {
                return
            }

            if (!this.podeRemoverVagaProjeto(item)) {
                mostraErro('', 'Não é possível remover vínculos com vagas preenchidas.')
                return
            }

            if (this.editando && item.id && !item.novo) {
                this.form.vagas_projetoDelete.push(item.id)
            }

            this.form.vagas_projeto.splice(index, 1)
        },
        normalizarVagasProjetoResposta(data) {
            const lista = data?.vagas_projeto || data?.VagasProjeto || []

            return lista.map((item) => ({
                id: item.id,
                projeto_id: item.projeto_id,
                vaga_aberta_id: item.vaga_aberta_id,
                empresa_id: item.empresa_id,
                qnt_total: Number(item.qnt_total || 0),
                qnt_preenchida: Number(item.qnt_preenchida || 0),
                vaga_aberta: this.normalizarVagaAberta(item.vaga_aberta || item.VagaAberta)
            }))
        },
        validarVagasProjetoFormulario() {
            if (this.totalRestanteVagas < 0) {
                mostraErro('', 'A soma distribuída nas vagas abertas não pode ser maior que o total do projeto.')
                return false
            }

            const minimoProjeto = this.quantidadeMinimaProjetoTotal
            if (Number(this.form.qnt_total || 0) < minimoProjeto) {
                mostraErro('', `A quantidade total do projeto não pode ser menor que ${minimoProjeto}.`)
                return false
            }

            for (let index = 0; index < this.form.vagas_projeto.length; index++) {
                const item = this.form.vagas_projeto[index]
                const minimo = this.quantidadeMinimaVagaLinha(item)
                const maximo = this.quantidadeMaximaVagaLinha(item)
                const valor = Number(item.qnt_total || 0)

                if (valor < minimo) {
                    mostraErro('', `A quantidade na linha #${index + 1} não pode ser menor que ${minimo} (preenchidas).`)
                    return false
                }

                if (valor > maximo) {
                    mostraErro('', `A quantidade na linha #${index + 1} não pode ser maior que ${maximo}.`)
                    return false
                }
            }

            return true
        },
        tratarErroSalvar(error, acao) {
            this.preloadAjax = false
            const erros = error?.response?.data?.erros
            const msg = error?.response?.data?.msg || `Erro ao ${acao} projeto.`

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

        selecionaVaga(obj) {
            const vagas_projeto = {}
            vagas_projeto.novo = true
            vagas_projeto.vaga_aberta_id = obj.id
            vagas_projeto.empresa_id = obj.empresa_id
            vagas_projeto.projeto_id = null
            vagas_projeto.qnt_total = Math.max(1, Number(this.totalRestanteVagas || 1))
            vagas_projeto.qnt_preenchida = 0
            vagas_projeto.vaga_aberta = this.normalizarVagaAberta(obj)

            let atual = this.form.vagas_projeto.findIndex((val) => val.vaga_aberta_id === vagas_projeto.vaga_aberta_id)

            if (atual < 0) {
                this.form.vagas_projeto.unshift(vagas_projeto)
            } else {
                mostraErro('', `A vaga ${vagas_projeto.vaga_aberta.titulo} já está na lista.`)
            }

            this.form.autocomplete_label_vaga_aberta = ''
        },

        formNovo() {
            this.cadastrado = false
            this.atualizado = false
            this.editando = false

            this.tituloJanela = 'Cadastrando Projeto'

            formReset()
            setupCampo()

            this.form = _.cloneDeep(this.formDefault)
            this.form.vagas_projetoDelete = []
            this.leitura = false
        },
        cadastrar() {
            formReset()

            $('#janelaCadastrar :input:enabled').trigger('blur')

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            if (!this.validarVagasProjetoFormulario()) {
                return false
            }

            this.preloadAjax = true
            this.form.qnt_total_restante = this.totalRestanteVagas

            axios
                .post(`${URL_ADMIN}/cadastro/projetos`, this.form)
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
            this.tituloJanela = 'Alterando Projeto'
            this.preloadAjax = true
            formReset()

            this.form = _.cloneDeep(this.formDefault)
            this.leitura = true

            return axios
                .get(`${URL_ADMIN}/cadastro/projetos/${id}/editar`)
                .then((response) => {
                    Object.assign(this.form, response.data)
                    this.form.vagas_projeto = this.normalizarVagasProjetoResposta(response.data)
                    this.form.vagas_projetoDelete = []
                    this.form.autocomplete_label_vaga_aberta = ''
                    this.editando = true
                    this.preloadAjax = false
                    setupCampo()
                    return true
                })
                .catch((error) => {
                    this.tratarErroSalvar(error, 'carregar')
                    return false
                })
        },

        alterar() {
            formReset()
            $('#janelaCadastrar :input:enabled').trigger('blur')

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            if (!this.validarVagasProjetoFormulario()) {
                return false
            }

            this.form._method = 'PUT'
            this.preloadAjax = true
            this.form.qnt_total_restante = this.totalRestanteVagas

            axios
                .put(`${URL_ADMIN}/cadastro/projetos/${this.form.id}`, this.form)
                .then((response) => {
                    this.preloadAjax = false
                    this.atualizado = true
                    this.atualizar()
                })
                .catch((error) => this.tratarErroSalvar(error, 'alterar'))
        },

        carregou(dados) {
            if (!dados || typeof dados !== 'object' || Array.isArray(dados)) {
                this.controle.carregando = false
                return
            }

            this.lista = dados.itens || []
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
