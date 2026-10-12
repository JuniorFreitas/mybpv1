import { createApp } from 'vue'
import { registerGlobals } from '../../../../registerGlobals'
import upload from '../../../../components/Upload'
import validacoes from '../../../../mixins/Validacoes'
import visualizadorPdf from '../../../../components/visualizadorPdf.vue'
import AcaoAssinaturaDocumento from '../../../../components/administracao/documentoassinatura/AcaoAssinaturaDocumento.vue'
import FiltroListagem from '../../../../components/ui/FiltroListagem'
import ComboboxAutoComplete from '../../../../components/ComboboxAutoComplete'
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
    temFiltrosPreenchidos,
    limparFiltrosListagem
} from '../../../../utils/listagemQueryParams'

const CAMPOS_FILTRO_URL = ['campoBusca', 'status', 'order']
const PAGES_DEFAULT = 50

const app = createApp({
    mixins: [validacoes],
    components: {
        upload,
        visualizadorPdf,
        AcaoAssinaturaDocumento,
        FiltroListagem,
        ComboboxAutoComplete,
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
        temFiltrosAtivos() {
            return temFiltrosPreenchidos(this.controle.dados, CAMPOS_FILTRO_URL)
        }
    },
    mounted() {
        lerFiltrosDaUrl(this.controle.dados, CAMPOS_FILTRO_URL)
        if (!this.controle.dados.order) {
            this.controle.dados.order = 'data_atualizacao'
        }

        const paginaInicial = lerPaginacaoDaUrl(this.controle.dados, { pagesDefault: PAGES_DEFAULT })
        this.$nextTick(() => {
            aplicarPaginaInicialListagem(this, paginaInicial)
            buscarListagem(this, { resetPagina: false })
        })
        criarWatchQueryParams(this, {
            camposFiltro: CAMPOS_FILTRO_URL,
            getDados: () => this.controle.dados
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
            if (event?.target?.closest?.('.dropdown')) {
                return
            }
            this.dropdownAbertoKey = null
        },
        chaveStatus(status) {
            if (status === 'Aceito pelo RH') return 'aprovado'
            if (status === 'Aguardando RH') return 'rh'
            if (status === 'Pendente Anexo') return 'pendente'
            if (status === 'Recusado pelo RH') return 'reprovado'
            if (status === 'Expirado') return 'neutro'
            return 'aberto'
        },
        textoStatus(status) {
            return this.textoCampo(status) || '—'
        },
        classeCorpoStatus(status) {
            return `mybp-card-corpo--${this.chaveStatus(status)}`
        },
        fluxoSteps(item) {
            const status = item?.status || ''
            const candidatoStatus = (() => {
                if (status === 'Expirado') return 'cancelado'
                if (status === 'Pendente Anexo') return 'aguardando'
                return 'aprovado'
            })()
            const rhStatus = (() => {
                if (status === 'Aceito pelo RH') return 'aprovado'
                if (status === 'Recusado pelo RH') return 'reprovado'
                if (status === 'Aguardando RH') return 'aguardando'
                if (status === 'Expirado') return 'cancelado'
                return 'pendente'
            })()

            return [
                {
                    key: 'envio',
                    label: 'Envio',
                    status: 'aprovado',
                    nome: this.textoCampo(item?.criador_nome) || 'Não informado',
                    data: this.textoCampo(item?.envio_em || item?.ultima_atualizacao)
                },
                {
                    key: 'candidato',
                    label: 'Candidato',
                    status: candidatoStatus,
                    statusTexto: status === 'Pendente Anexo'
                        ? 'Aguardando'
                        : status === 'Expirado'
                            ? 'Expirado'
                            : 'Enviado'
                },
                {
                    key: 'rh',
                    label: 'RH',
                    status: rhStatus,
                    statusTexto: status === 'Aceito pelo RH'
                        ? 'Aceito'
                        : status === 'Recusado pelo RH'
                            ? 'Recusado'
                            : status === 'Aguardando RH'
                                ? 'Aguardando'
                                : 'Pendente'
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
        onSelectStatus() {
            this.atualizar()
        },
        onSelectOrder() {
            this.atualizar()
        },
        limparFiltros() {
            limparFiltrosListagem(this.controle.dados, CAMPOS_FILTRO_URL)
            this.controle.dados.order = 'data_atualizacao'
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
            let endpoint = ''
            switch (window.location.hostname) {
                case 'qa.mybp.com.br':
                    endpoint = `https://qasgi.bpse.com.br/api/carta-oferta/${token}/integramybp`
                    break
                case 'sistema.mybp.com.br':
                    endpoint = `https://sgi.bpse.com.br/api/carta-oferta/${token}/integramybp`
                    break
                default:
                    endpoint = `http://localhost:8884/api/carta-oferta/${token}/integramybp`
                    break
            }

            await axios
                .post(
                    `${endpoint}/`,
                    {},
                    {
                        headers: {
                            'X-API-TOKEN': 'gTyF2ErmclLMRjzxBHo20OoXVqNhgnDKqCtQVRtsrfF1sOU4s6wK'
                        }
                    }
                )
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
            sincronizarFiltrosNaUrl(this.controle.dados, CAMPOS_FILTRO_URL)
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
app.mount('#app')
