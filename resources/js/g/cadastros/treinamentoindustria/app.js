import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import TreinamentoIndustria from '../../../components/cadastros/treinamentoindustria/TreinamentoIndustria'

const app = createApp({
    data() {
        return {}
    },
    components: {
        TreinamentoIndustria
    }
})

registerGlobals(app)
app.mount('#app')
