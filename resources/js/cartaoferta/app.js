import { createApp } from 'vue'
import { registerGlobals } from '../registerGlobals'
import Upload from '../components/Upload.vue'
import AcessoPorCodigo from '../components/publico/AcessoPorCodigo.vue'

const app = createApp({
    components: {
        Upload,
        AcessoPorCodigo
    },
    data() {
        return {
            preload: true,
            msgPreload: 'Carregando...',
            erro: '',
            dados: {
                autenticado: false,
                status: '',
                public_id: '',
                candidato: null,
                cargo: '',
                empresa: {},
                curriculo_id: null
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
