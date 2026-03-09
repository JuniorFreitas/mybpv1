import { createApp } from 'vue'
import { registerGlobals } from '../../registerGlobals'
import TreinamentosCarteiraEtiquetas from '../../components/treinamentos-carteira-etiquetas/TreinamentosCarteiraEtiquetas.vue'

const app = createApp({
    components: {
        TreinamentosCarteiraEtiquetas
    }
})
registerGlobals(app)
app.mount('#app')
