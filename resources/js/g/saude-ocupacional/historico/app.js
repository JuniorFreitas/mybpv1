import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import HistoricoAso from '../../../components/saude-ocupacional/historico/HistoricoAso'

const app = createApp({
    components: {
        HistoricoAso
    }
})

registerGlobals(app)
app.mount('#app')
