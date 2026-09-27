import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import CentroCustoRelatorio from '../../../components/relatorios/centrodecusto/CentroCusto.vue'

const app = createApp({
    components: {
        CentroCustoRelatorio
    }
})

registerGlobals(app)
app.mount('#app')
