import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import RelatorioSintetico from '../../../components/controle-ponto/relatorio-sintetico/RelatorioSintetico'

const app = createApp({
    data() {
        return {}
    },
    components: {
        RelatorioSintetico
    }
})

registerGlobals(app)
app.mount('#app')
