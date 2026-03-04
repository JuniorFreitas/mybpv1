import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import VencimentoFerias from '../../../components/relatorios/vencimentoferias'

const app = createApp({
    data() {
        return {}
    },
    components: {
        VencimentoFerias
    }
})

registerGlobals(app)
app.mount('#app')
