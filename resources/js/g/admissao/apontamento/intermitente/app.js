import { createApp } from 'vue'
import { registerGlobals } from '../../../../registerGlobals'
import Intermitente from '../../../../components/admissao/apontamento/Intermitente'

const app = createApp({
    data() {
        return {}
    },
    components: {
        Intermitente
    }
})

registerGlobals(app)
app.mount('#app')
