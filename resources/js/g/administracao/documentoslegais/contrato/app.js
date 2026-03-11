import { createApp } from 'vue'
import { registerGlobals } from '../../../../registerGlobals'
import contrato from '../../../../components/administracao/documentoslegais/contrato'

const app = createApp({
    components: {
        contrato
    }
})

registerGlobals(app)
app.mount('#app')
