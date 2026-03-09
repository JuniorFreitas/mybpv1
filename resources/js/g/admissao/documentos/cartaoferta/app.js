import { createApp } from 'vue'
import { registerGlobals } from '../../../../registerGlobals'
import upload from '../../../../components/Upload'
import validacoes from '../../../../mixins/Validacoes'
import visualizadorPdf from '../../../../components/visualizadorPdf.vue'
import AcaoAssinaturaDocumento from '../../../../components/administracao/documentoassinatura/AcaoAssinaturaDocumento.vue'
const abrirModal = (selector) => {
    if (typeof $ === 'undefined') return
    $(selector).modal('show')
}

const fecharModal = (selector) => {
    if (typeof $ === 'undefined') return
    $(selector).modal('hide')
}



const app = createApp({
    mixins: [validacoes],
    components: {
        upload,
        visualizadorPdf,
        AcaoAssinaturaDocumento
    },
    data() {
        return {
            tituloJanela: 'Carta Oferta',
            preload: false,
            atualizando: false,

            anexoUploadAndamento: false,
            urlAnexoUpload: `${URL_SITE}/documentos/uploadAnexos`,

            hash: `${parseInt(Math.random() * 999999)}`,

            todos_municipios: `autocomplete/todos-municipios`,
            preloadbotoes: true,
            assinaturaDigitalHabilitada: typeof window !== 'undefined' ? !!window.MYBP_ASSINATURA_DIGITAL_HABILITADA : true,

            URL_ADMIN,
            objopen: null,
            abriupdf: false,

            lista: [],
            lista_status: [],
            lista_projetos: [],

            vagas: [],
            areasEtiquetas: [],

            controle: {
                carregando: false,
                dados: {
                    filtroPeriodo: false,
                    periodo: '',
                    caminho_autocomplete: `autocomplete/todas-vagas-ativas`,

                    autocomplete_vaga_label_anterior: '',
                    autocomplete_vaga_label: '',

                    pages: 50,
                    campoBusca: '',
                    status: '',
                    curriculo_id: '',

                    projeto_id: '',
                    vaga_projeto_id: '',

                    order: 'nome'
                }
            }
        }
    },
    computed: {
        urlDefault() {
            return `${URL_ADMIN}/admissao/documentos/carta-oferta`
        },
        urlPaginacao() {
            return `${this.urlDefault}/atualizar`
        },
        filterVagasProjeto() {
            return this.lista_projetos.filter((item) => item.id === this.controle.dados.projeto_id) ?? []
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.atualizar()
        this.listaVagas()
    },
    methods: {
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
            const nome = item && item.curriculo && item.curriculo.nome ? item.curriculo.nome : ''
            return nome ? `Carta Oferta - ${nome}` : 'Carta Oferta'
        },
        getSignatariosIniciaisAssinaturaCartaOferta(item) {
            const nome = item && item.curriculo && item.curriculo.nome ? item.curriculo.nome : ''
            const email = item && item.curriculo && item.curriculo.email ? item.curriculo.email : ''
            const cpf = item && item.curriculo && item.curriculo.cpf ? item.curriculo.cpf : ''
            return [{ nome, email, cpf }]
        },
        enviarAssinaturaCartaOferta({ contexto }) {
            return axios.post(`${this.urlDefault}/enviar-para-assinatura`, { carta_oferta_id: contexto.id })
        },

        //GERAL
        resetaCampoVaga() {
            if (this.controle.dados.autocomplete_vaga_label_anterior !== this.controle.dados.autocomplete_vaga_label) {
                this.controle.dados.autocomplete_vaga_label_anterior = ''
                this.controle.dados.autocomplete_vaga_label = ''
                this.controle.dados.vagas_abertas_id = ''
            }
        },

        selecionaVaga(obj) {
            this.controle.dados.vagas_abertas_id = obj.id
            this.controle.dados.autocomplete_vaga_label = obj.label
            this.controle.dados.autocomplete_vaga_label_anterior = obj.label
        },

        async formVisualizar(obj) {
            this.objopen = null
            this.abriupdf = false
            this.objopen = obj
            this.atualizanndo = false
            this.$nextTick(() => {
                this.abriupdf = true
            })
            await this.getIntegraMybp(obj.token)
        },

        carregouPdf() {
            this.preloadbotoes = false
        },

        async responder(obj, resposta) {
            this.atualizanndo = true
            this.preload = true
            obj.resposta = resposta

            await axios
                .put(`${this.urlDefault}/responder`, obj)
                .then((response) => {
                    if (resposta === 'Recusado pelo RH') {
                        fecharModal('#janelaRecusar')
                    }
                    fecharModal('#janelaVisualizar')
                    mostraSucesso('', 'Resposta computada com sucesso')
                    this.preload = false
                    this.atualizando = false
                    this.atualizar()
                })
                .catch((error) => (this.preload = false))
        },

        listaVagas() {
            this.preload = true
            $.get(`${URL_PUBLICO}/lista-vagas`)
                .done((data) => {
                    this.preload = false
                    this.vagas = data.vagas
                })
                .fail((data) => {
                    this.preload = false
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
                    this.objopen.integraMybp = data
                })
                .catch((error) => {
                    this.preload = false
                    mostraErro('', 'Erro ao integrar caso o erro persista, entre em contato com o suporte.')
                    return false
                })
        },

        carregou(dados) {
            this.lista = dados.itens
            this.lista_projetos = dados.lista_projetos
            this.lista_status = dados.lista_status
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        }
    }
})

registerGlobals(app)
app.mount('#app')
