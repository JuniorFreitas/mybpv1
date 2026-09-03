import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import GestorAprovacaoConfig from '../../../components/administracao/gestor-aprovacao-config/GestorAprovacaoConfig'

const app = createApp({
    data() {
        return {}
    },
    components: {
        GestorAprovacaoConfig
    }
})

registerGlobals(app)
app.mount('#app')
