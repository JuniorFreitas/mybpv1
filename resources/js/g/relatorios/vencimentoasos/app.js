import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import VencimentoAsos from '../../../components/relatorios/vencimentoasos/VencimentoAsos'

const app = createApp({
    data() {
        return {}
    },
    components: {
        VencimentoAsos
    }
})

registerGlobals(app)
app.mount('#app')
