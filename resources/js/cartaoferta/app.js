import { createApp } from 'vue'
import { registerGlobals } from '../registerGlobals'
import Upload from '../components/Upload.vue'
import AcessoPorCodigo from '../components/publico/AcessoPorCodigo.vue'
import VisualizadorPdf from '../components/visualizadorPdf.vue'

const app = createApp({
    components: {
        Upload,
        AcessoPorCodigo,
        VisualizadorPdf
    },
    data() {
        return {
            preload: true,
            msgPreload: 'Carregando...',
            erro: '',
            // Aceite eletrônico (empresas sem assinatura digital)
            consentimento: false,
            recusando: false,
            motivoRecusa: '',
            respondendo: false,
            dados: {
                autenticado: false,
                status: '',
                public_id: '',
                candidato: null,
                cargo: '',
                empresa: {},
                curriculo_id: null,
                aceite_eletronico: false,
                aceite: null,
                textos_aceite: {}
            },
            apelido: '',
            anexos: [],
            anexosDel: [],
            anexoUploadAndamento: false,
            urlAnexoUpload: `${URL_SITE}/documentos/uploadAnexos`
        }
    },
    computed: {
        urlBase() {
            return `${URL_SITE}/${this.apelido}/carta-oferta/${this.dados.public_id}`
        },
        urlProposta() {
            return `${this.urlBase}/proposta.pdf`
        },
        urlComprovante() {
            return `${this.urlBase}/comprovante.pdf`
        },
        // Sem assinatura digital: primeiro o candidato lê a proposta e aceita ou recusa.
        aguardandoDecisao() {
            return this.dados.aceite_eletronico && !this.dados.aceite && this.dados.status === 'Pendente Anexo'
        },
        aceitou() {
            return this.dados.aceite?.decisao === 'aceito'
        },
        // O anexo só aparece depois do aceite (ou direto, quando a empresa não usa aceite eletrônico).
        podeAnexar() {
            return this.dados.status === 'Pendente Anexo' && (!this.dados.aceite_eletronico || this.aceitou)
        }
    },
    mounted() {
        const pathArray = window.location.pathname.split('/')
        this.apelido = pathArray[1] || ''
        const raw = document.getElementById('dd')
        if (raw) {
            this.dados = Object.assign(this.dados, JSON.parse(raw.value || '{}'))
            raw.remove()
        }
        if (!this.dados.public_id) {
            this.dados.public_id = pathArray[3] || ''
        }
        this.preload = false
    },
    methods: {
        aoAutenticar(payload) {
            this.dados = Object.assign(this.dados, payload, { autenticado: true })
        },
        async responder(decisao) {
            this.erro = ''
            if (decisao === 'aceitar' && !this.consentimento) {
                this.erro = 'Marque a declaração de aceite para continuar.'
                return
            }
            this.respondendo = true
            try {
                const corpo = decisao === 'aceitar' ? { consentimento: true } : { motivo: this.motivoRecusa }
                const { data } = await axios.post(`${this.urlBase}/${decisao}`, corpo)
                this.dados = Object.assign(this.dados, data)
                this.recusando = false
            } catch (err) {
                this.erro = err?.response?.data?.message || 'Não foi possível registrar sua resposta. Tente novamente.'
            } finally {
                this.respondendo = false
            }
        },
        salvar() {
            this.preload = true
            this.msgPreload = 'Salvando...'
            axios
                .post(`${this.urlBase}/salvar`, { arquivo: this.anexos[0] })
                .then(() => {
                    window.location.reload(true)
                })
                .catch((error) => {
                    this.preload = false
                    this.erro = error?.response?.data?.message || 'Não foi possível enviar a carta.'
                })
        }
    }
})

registerGlobals(app)
app.mount('#app')
