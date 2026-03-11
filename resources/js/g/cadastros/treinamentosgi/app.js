import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import TreinamentoSgi from '../../../components/cadastros/treinamentosgi/TreinamentoSgi'

const app = createApp({
    data() {
        return {}
    },
    components: {
        TreinamentoSgi
    }
})

registerGlobals(app)
app.mount('#app')
