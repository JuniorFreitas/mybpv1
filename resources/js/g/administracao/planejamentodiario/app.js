import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import PlanejamentoDiario from '../../../components/administracao/planejamentodiario/PlanejamentoDiario'

const app = createApp({
    data() {
        return {}
    },
    components: {
        PlanejamentoDiario
    }
})

registerGlobals(app)
app.mount('#app')
