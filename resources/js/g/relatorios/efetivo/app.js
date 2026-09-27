import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import EfetivoRelatorio from '../../../components/relatorios/efetivo/Efetivo.vue'

const app = createApp({
    components: {
        EfetivoRelatorio
    }
})

registerGlobals(app)
app.mount('#app')
