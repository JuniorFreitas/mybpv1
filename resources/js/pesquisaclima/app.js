import { createApp } from 'vue'
import { registerGlobals } from '../registerGlobals'
import PesquisaClima from '../components/pesquisaclima/PesquisaClima'

const app = createApp({
    components: {
        PesquisaClima
    },
    data() {
        return {
            autenticado: false,
            preload: true,
            curriculo: null,

            formUser: {
                login: '',
                password: ''
            }
        }
    },
    mounted() {
        this.formVinculoDefault = _.cloneDeep(this.formVinculo) //copia
    },
    methods: {
        autenticar() {
            this.autenticado = false
            this.preloadAutenticacao = true
            axios
                .post(`${URL_SITE}/pesquisaclima/autenticar`, this.formUser)
                .then((response) => {
                    let data = response.data
                    this.curriculo = data.curriculo
                    this.autenticado = data.autenticado
                    // setTimeout(() => {
                    //     $("#componenteDocumentos").modal('show');
                    // }, 100)

                    this.preloadAutenticacao = false
                })
                .catch((error) => {
                    this.autenticado = false
                    this.preloadAutenticacao = false
                })
        }
    }
})

registerGlobals(app)
app.mount('#app')
