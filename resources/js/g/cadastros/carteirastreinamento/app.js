import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import CarteirasEtiquetasTemplatesCadastro from '../../../components/cadastros/carteirasetiquetas/CarteirasEtiquetasTemplatesCadastro'

const app = createApp({
    components: {
        CarteirasEtiquetasTemplatesCadastro
    }
})

registerGlobals(app)
app.mount('#app')
