import { createApp } from 'vue'
import { registerGlobals } from '../../../../registerGlobals'
import upload from '../../../../components/Upload'
import validacoes from '../../../../mixins/Validacoes'
import visualizadorPdf from '../../../../components/visualizadorPdf.vue'
import AcaoAssinaturaDocumento from '../../../../components/administracao/documentoassinatura/AcaoAssinaturaDocumento.vue'
import FiltroListagem from '../../../../components/ui/FiltroListagem'
import ComboboxAutoComplete from '../../../../components/ComboboxAutoComplete'
import DateRangeFilter from '../../../../components/DateRangeFilter.vue'
import MybpCardCampo from '../../../../components/ui/MybpCardCampo.vue'
import MybpFluxoAprovacao from '../../../../components/ui/MybpFluxoAprovacao.vue'
import MybpStatusBadge from '../../../../components/ui/MybpStatusBadge.vue'
import {
    lerFiltrosDaUrl,
    lerPaginacaoDaUrl,
    sincronizarFiltrosNaUrl,
    criarWatchQueryParams,
    aplicarPaginaInicialListagem,
    buscarListagem,
    limparFiltrosListagem,
    montarExtrasPaginacao
} from '../../../../utils/listagemQueryParams'

const CAMPOS_FILTRO_URL = ['campoBusca', 'status', 'order', 'dataInicio', 'dataFim']
const PAGES_DEFAULT = 50

const app = createApp({
    mixins: [validacoes],
    components: {
        upload,
        visualizadorPdf,
        AcaoAssinaturaDocumento,
        FiltroListagem,
        ComboboxAutoComplete,
        DateRangeFilter,
        MybpCardCampo,
        MybpFluxoAprovacao,
        MybpStatusBadge
    },
    data() {
        return {
            tituloJanela: 'Carta Oferta',
            preload: false,
            atualizando: false,
            criando: false,

            hash: `${parseInt(Math.random() * 999999)}`,
            assinaturaDigitalHabilitada: typeof window !== 'undefined' ? !!window.MYBP_ASSINATURA_DIGITAL_HABILITADA : true,

            URL_ADMIN,
            objopen: null,
            abriupdf: false,

            lista: [],
            lista_status: [],
            lista_projetos: [],

            nova: {
                curriculo_id: '',
                vagas_abertas_id: '',
                vaga_projeto_id: '',
                setor: '',
                salario: '',
                data_inicio: '',
                autocomplete_curriculo_label: '',
                autocomplete_vaga_label: ''
            },
            opcoesProjetoNova: [],

            controle: {
                carregando: false,
                dados: {
                    filtroPeriodo: false,
                    dataInicio: '',
                    dataFim: '',
                    periodo: '',
                    pages: PAGES_DEFAULT,
                    campoBusca: '',
                    status: '',
                    order: 'data_atualizacao',
                    curriculo_id: '',
                    projeto_id: '',
                    vaga_projeto_id: ''
                }
            },
            dropdownAbertoKey: null
        }
    },
    computed: {
        urlDefault() {
            return `${URL_ADMIN}/admissao/documentos/carta-oferta`
        },
        urlPaginacao() {
            return `${this.urlDefault}/atualizar`
        },
        caminhoCurriculos() {
            return 'admissao/documentos/carta-oferta/buscar-curriculos'
        },
        filtroStatusOpcoes() {
            const base = [{ value: '', label: 'Todos' }]
            return base.concat((this.lista_status || []).map((s) => ({ value: s, label: s })))
        },
        filtroOrderOpcoes() {
            return [
                { value: 'data_atualizacao', label: 'Data de atualização' },
                { value: 'nome', label: 'Nome' }
            ]
        },
        filtroPagesOpcoes() {
            return [
                { value: 20, label: '20' },
                { value: 50, label: '50' },
                { value: 100, label: '100' }
            ]
        },
        temFiltrosAtivos() {
            const d = this.controle.dados
            return !!(
                d.campoBusca ||
                d.status ||
                (d.order && d.order !== 'data_atualizacao') ||
                (d.filtroPeriodo && d.dataInicio && d.dataFim) ||
                Number(d.pages) !== PAGES_DEFAULT
            )
        }
    },
    watch: {
        'controle.dados': criarWatchQueryParams(CAMPOS_FILTRO_URL, { pagesDefault: PAGES_DEFAULT })
    },
    mounted() {
        lerFiltrosDaUrl(this.controle.dados, CAMPOS_FILTRO_URL)
        if (!this.controle.dados.order) {
            this.controle.dados.order = 'data_atualizacao'
        }
        if (this.controle.dados.dataInicio || this.controle.dados.dataFim) {
            this.controle.dados.filtroPeriodo = true
        }
        this.sincronizarPeriodoString()

        const paginaInicial = lerPaginacaoDaUrl(this.controle.dados, { pagesDefault: PAGES_DEFAULT })
        this.$nextTick(() => {
            aplicarPaginaInicialListagem(this, paginaInicial)
            buscarListagem(this, { resetPagina: false })
        })
        document.addEventListener('click', this.onClickOutside)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },
    methods: {
        textoCampo(valor) {
            if (valor === null || valor === undefined) return ''
            const texto = String(valor).trim()
            return texto
        },
        primeiroUltimoNome(valor) {
            const partes = this.textoCampo(valor).split(/\s+/).filter(Boolean)
            if (partes.length === 0) return ''
            if (partes.length === 1) return partes[0]
            return `${partes[0]} ${partes[partes.length - 1]}`
        },
        nomeCandidato(item) {
            return this.textoCampo(item?.candidato_nome) || 'Sem nome'
        },
        emailExibicao(item) {
            return this.textoCampo(item?.contato_email)
        },
        telefoneExibicao(item) {
            return this.textoCampo(item?.contato_telefone)
        },
        cpfExibicao(item) {
            return this.textoCampo(item?.cpf_mascarado)
        },
        nomeCargo(item) {
            return this.textoCampo(item?.cargo_nome)
        },
        nomeProjeto(item) {
            return this.textoCampo(item?.projeto_nome)
        },
        toggleDropdown(itemId) {
            if (!itemId) return
            const key = `carta_oferta:${itemId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(itemId) {
            return this.dropdownAbertoKey === `carta_oferta:${itemId}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        onClickOutside(event) {
            if (event?.target?.closest?.('.dropdown') || event?.target?.closest?.('.mybp-combobox-wrap')) {
                return
            }
            this.dropdownAbertoKey = null
            this.fecharOutrosComboboxes(null)
        },
        chaveStatus(status) {
            if (status === 'Aceito pelo RH') return 'aprovado'
            if (status === 'Aguardando RH') return 'rh'
            if (status === 'Pendente Anexo') return 'pendente'
            if (status === 'Recusado pelo RH') return 'reprovado'
            if (status === 'Recusado pelo candidato') return 'reprovado'
            if (status === 'Expirado') return 'neutro'
            return 'aberto'
        },
        textoStatus(status) {
            return this.textoCampo(status) || '—'
        },
        classeCorpoStatus(status) {
            return `mybp-card-corpo--${this.chaveStatus(status)}`
        },
        passoFluxo(fluxo, fallbackStatus, fallbackTexto) {
            const passo = fluxo && typeof fluxo === 'object' ? fluxo : {}
            const status = this.textoCampo(passo.status) || fallbackStatus
            const texto = this.textoCampo(passo.texto) || fallbackTexto
            const nome = this.primeiroUltimoNome(passo.nome)
            const data = this.textoCampo(passo.data)

            return {
                status,
                statusTexto: texto,
                nome: nome || undefined,
                data: data || undefined
            }
        },
        fluxoSteps(item) {
            const candidato = this.passoFluxo(item?.fluxo_candidato, 'aguardando', 'Aguardando')
            const rh = this.passoFluxo(item?.fluxo_rh, 'pendente', 'Pendente')

            return [
                {
                    key: 'envio',
                    label: 'Envio',
                    status: 'aprovado',
                    nome: this.primeiroUltimoNome(item?.criador_nome) || 'Não informado',
                    data: this.textoCampo(item?.envio_em || item?.ultima_atualizacao)
                },
                {
                    key: 'candidato',
                    label: 'Candidato',
                    status: candidato.status,
                    statusTexto: candidato.statusTexto,
                    nome: candidato.nome,
                    data: candidato.data
                },
                {
                    key: 'rh',
                    label: 'RH',
                    status: rh.status,
                    statusTexto: rh.statusTexto,
                    nome: rh.nome,
                    data: rh.data
                }
            ]
        },
        async copiarLinkPublico(item) {
            const link = item?.link_publico
            if (!link) {
                mostraErro('', 'Link público indisponível.')
                return
            }
            try {
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    await navigator.clipboard.writeText(link)
                } else {
                    const input = document.createElement('textarea')
                    input.value = link
                    document.body.appendChild(input)
                    input.select()
                    document.execCommand('copy')
                    document.body.removeChild(input)
                }
                mostraSucesso('', 'Link do candidato copiado.')
            } catch (e) {
                mostraErro('', 'Não foi possível copiar o link.')
            }
        },
        temDocumentoAssinatura(item) {
            const doc = item && item.documento_para_assinatura
            return !!(doc && doc.id)
        },
        abrirEnvioAssinaturaCartaOferta(item) {
            this.$refs.acaoAssinaturaCartaOferta.abrirEnvio(item)
        },
        abrirGerenciamentoAssinaturaCartaOferta(item) {
            const doc = item && item.documento_para_assinatura
            if (!doc || !doc.id) return
            this.$refs.acaoAssinaturaCartaOferta.abrirGerenciar(doc, item)
        },
        getNomeDocumentoAssinaturaCartaOferta(item) {
            const nome = this.nomeCandidato(item)
            return nome && nome !== 'Sem nome' ? `Carta Oferta - ${nome}` : 'Carta Oferta'
        },
        getSignatariosIniciaisAssinaturaCartaOferta(item) {
            const nome = this.nomeCandidato(item)
            const email = this.emailExibicao(item)
            const cpf = item?.cpf || ''
            return [{ nome: nome === 'Sem nome' ? '' : nome, email, cpf }]
        },
        enviarAssinaturaCartaOferta({ contexto }) {
            return axios.post(`${this.urlDefault}/enviar-para-assinatura`, { carta_oferta_id: contexto.id })
        },
        formatarDataFiltro(valor) {
            const raw = String(valor || '').trim()
            if (!raw) return ''
            if (/^\d{2}\/\d{2}\/\d{4}$/.test(raw)) return raw
            if (/^\d{4}-\d{2}-\d{2}$/.test(raw)) {
                const [y, m, d] = raw.split('-')
                return `${d}/${m}/${y}`
            }
            return raw
        },
        sincronizarPeriodoString() {
            const d = this.controle.dados
            if (d.filtroPeriodo && d.dataInicio && d.dataFim) {
                d.periodo = `${this.formatarDataFiltro(d.dataInicio)} até ${this.formatarDataFiltro(d.dataFim)}`
            } else {
                d.periodo = ''
            }
        },
        onPeriodoChange() {
            this.sincronizarPeriodoString()
            this.atualizar()
        },
        onSubmitFiltro() {
            this.sincronizarPeriodoString()
            this.atualizar()
        },
        onSelectFiltro() {
            this.sincronizarPeriodoString()
            this.atualizar()
        },
        fecharOutrosComboboxes(manter) {
            const combos = [
                ['filtro-status', 'comboFiltroStatus'],
                ['filtro-order', 'comboFiltroOrder'],
                ['filtro-pages', 'comboFiltroPages']
            ]
            combos.forEach(([id, refName]) => {
                if (id !== manter && this.$refs[refName]?.close) {
                    this.$refs[refName].close()
                }
            })
        },
        limparFiltros() {
            limparFiltrosListagem(this.controle.dados, CAMPOS_FILTRO_URL)
            this.controle.dados.order = 'data_atualizacao'
            this.controle.dados.filtroPeriodo = false
            this.controle.dados.dataInicio = ''
            this.controle.dados.dataFim = ''
            this.controle.dados.periodo = ''
            this.controle.dados.pages = PAGES_DEFAULT
            this.fecharOutrosComboboxes(null)
            this.atualizar()
        },
        abrirNovaCarta() {
            this.nova = {
                curriculo_id: '',
                vagas_abertas_id: '',
                vaga_projeto_id: '',
                setor: '',
                salario: '',
                data_inicio: '',
                autocomplete_curriculo_label: '',
                autocomplete_vaga_label: ''
            }
            this.opcoesProjetoNova = []
            this.$refs.janelaNovaCarta?.abrirModal()
        },
        selecionaCurriculo(obj) {
            this.nova.curriculo_id = obj?.curriculo_id || obj?.id || ''
            this.nova.autocomplete_curriculo_label = obj?.label || ''
        },
        selecionaVagaNova(obj) {
            this.nova.vagas_abertas_id = obj?.id || ''
            this.nova.autocomplete_vaga_label = obj?.label || ''
            this.nova.vaga_projeto_id = ''
            this.opcoesProjetoNova = this.mapearProjetosDaVaga(obj)
        },
        mapearProjetosDaVaga(obj) {
            const lista = obj?.Projetos || obj?.projetos || []
            if (!Array.isArray(lista)) {
                return []
            }
            return lista
                .map((vp) => {
                    const id = vp?.id
                    const nome = vp?.Projeto?.nome || vp?.projeto?.nome || vp?.nome || ''
                    if (!id || !String(nome).trim()) {
                        return null
                    }
                    return {
                        value: id,
                        label: String(nome).trim()
                    }
                })
                .filter(Boolean)
        },
        salarioParaEnvio(valor) {
            if (valor === null || valor === undefined) {
                return null
            }
            const texto = String(valor).trim()
            if (!texto) {
                return null
            }
            const digitos = texto.replace(/\D/g, '')
            if (!digitos || Number(digitos) === 0) {
                return null
            }
            return texto
        },
        async criarCarta() {
            if (!this.nova.curriculo_id || !this.nova.vagas_abertas_id) {
                mostraErro('', 'Selecione o candidato e a vaga aberta.')
                return
            }
            this.criando = true
            try {
                await axios.post(`${this.urlDefault}/criar`, {
                    curriculo_id: this.nova.curriculo_id,
                    vagas_abertas_id: this.nova.vagas_abertas_id,
                    vaga_projeto_id: this.nova.vaga_projeto_id || null,
                    setor: this.nova.setor || null,
                    salario: this.salarioParaEnvio(this.nova.salario),
                    data_inicio: this.nova.data_inicio || null
                })
                mostraSucesso('', 'Carta oferta criada e envio iniciado.')
                this.$refs.janelaNovaCarta?.fecharModal()
                this.atualizar()
            } catch (err) {
                const msg = err?.response?.data?.message || 'Não foi possível criar a carta oferta.'
                mostraErro('', msg)
            } finally {
                this.criando = false
            }
        },
        async formVisualizar(obj) {
            this.objopen = null
            this.abriupdf = false
            this.preload = false
            this.atualizando = false

            const nome = this.nomeCandidato(obj)
            this.tituloJanela = nome && nome !== 'Sem nome'
                ? `Carta Oferta — ${nome}`
                : `Carta Oferta #${obj?.id || ''}`

            this.objopen = obj
            this.$nextTick(() => {
                this.abriupdf = !!(obj?.anexo?.url)
            })

            if (obj?.local === 'SGI' && obj?.token) {
                await this.getIntegraMybp(obj.token)
            }
        },
        async responder(obj, resposta) {
            this.atualizando = true
            this.preload = true
            obj.resposta = resposta

            await axios
                .put(`${this.urlDefault}/responder`, obj)
                .then(() => {
                    if (resposta === 'Recusado pelo RH') {
                        this.$refs.janelaRecusar?.fecharModal()
                    }
                    this.$refs.janelaVisualizar?.fecharModal()
                    mostraSucesso('', 'Resposta computada com sucesso')
                    this.preload = false
                    this.atualizando = false
                    this.atualizar()
                })
                .catch(() => {
                    this.preload = false
                    this.atualizando = false
                })
        },
        async getIntegraMybp(token) {
            // Proxy server-side: token SGI nunca vai no browser
            await axios
                .post(`${this.urlDefault}/${token}/integra-sgi`)
                .then(({ data }) => {
                    if (this.objopen) this.objopen.integraMybp = data
                })
                .catch(() => {
                    this.preload = false
                })
        },
        carregou(dados) {
            this.lista = dados.itens
            this.lista_projetos = dados.lista_projetos
            this.lista_status = dados.lista_status
            this.controle.carregando = false
            sincronizarFiltrosNaUrl(
                this.controle.dados,
                CAMPOS_FILTRO_URL,
                montarExtrasPaginacao(this, { pagesDefault: PAGES_DEFAULT })
            )
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.sincronizarPeriodoString()
            buscarListagem(this, { resetPagina: true })
        }
    }
})

registerGlobals(app)
app.mount('#app')
