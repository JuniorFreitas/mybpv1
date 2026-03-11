import { createApp } from 'vue'
import { registerGlobals } from '../../../../registerGlobals'
import documentoempresa from '../../../../components/administracao/documentoslegais/documentoempresa'

const app = createApp({
    components: {
        documentoempresa
    }
})

registerGlobals(app)
app.mount('#app')
