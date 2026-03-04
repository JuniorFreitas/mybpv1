import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import Historico from '../../../components/admissao/historico/Historico.vue'

const app = createApp({
    data() {
        return {}
    },
    components: {
        Historico
    }
})

registerGlobals(app)
app.mount('#app')
