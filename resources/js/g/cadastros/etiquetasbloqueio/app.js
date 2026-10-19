import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import EtiquetasBloqueioCadastro from '../../../components/cadastros/etiquetasbloqueio/EtiquetasBloqueioCadastro'

const app = createApp({
    components: {
        EtiquetasBloqueioCadastro
    }
})

registerGlobals(app)
app.mount('#app')
