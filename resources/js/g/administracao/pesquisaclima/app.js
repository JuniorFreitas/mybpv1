import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import PesquisaClima from '../../../components/administracao/pesquisaclima/PesquisaClima'

const app = createApp({
    data() {
        return {}
    },
    components: {
        PesquisaClima
    }
})

registerGlobals(app)
app.mount('#app')
