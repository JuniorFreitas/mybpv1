import { createApp } from 'vue'
import { registerGlobals } from '../registerGlobals'
import Upload from '../components/Upload.vue'

const app = createApp({
    components: {
        Upload
    },
    data() {
        return {
            autenticado: false,
            preload: true,
            msgPreload: 'Carregando...',
            curriculo: null,

            anexos: [],
            anexosDel: [],

            anexoUploadAndamento: false,
            urlAnexoUpload: `${URL_SITE}/documentos/uploadAnexos`,
            form: {}
        }
    },
    mounted() {
        const pathArray = window.location.pathname.split('/')
        this.form = JSON.parse(document.getElementById('dd').value)
        document.getElementById('dd').remove()
        this.form.apelido = pathArray[1]
        this.preload = false
    },
    methods: {
        salvar() {
            this.preload = true
            this.msgPreload = 'Salvando...'
            axios
                .post(`${URL_SITE}/${this.form.apelido}/carta-oferta/${this.form.token}/salvar`, {
                    arquivo: this.anexos[0]
                })
                .then((response) => {
                    this.preload = true
                    window.location.reload(true)
                })
                .catch((error) => {
                    this.preload = false
                    window.location.reload(true)
                })
        }
    }
})

registerGlobals(app)
app.mount('#app')
