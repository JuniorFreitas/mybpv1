import { createApp } from 'vue'
import { registerGlobals } from '../registerGlobals'
import Documento from '../components/documento/Documento'
import AcessoPorCodigo from '../components/publico/AcessoPorCodigo.vue'

const app = createApp({
    components: {
        Documento,
        AcessoPorCodigo
    },
    data() {
        return {
            apelido: '',
            dados: {
                autenticado: false,
                public_id: null,
                curriculo: null
            }
        }
    },
    computed: {
        urlBase() {
            return `${URL_SITE}/${this.apelido}/documentos-pre-admissao`
        },
        // Link com public_id: o backend confere se o CPF é o do link.
        extras() {
            return this.dados.public_id ? { public_id: this.dados.public_id } : {}
        }
    },
    mounted() {
        this.apelido = window.location.pathname.split('/')[1] || ''
        const raw = document.getElementById('dd')
        if (raw) {
            this.dados = Object.assign(this.dados, JSON.parse(raw.value || '{}'))
            raw.remove()
        }
    },
    methods: {
        aoAutenticar(payload) {
            this.dados = Object.assign(this.dados, payload, { autenticado: true })
        }
    }
})

registerGlobals(app)
app.mount('#app')
