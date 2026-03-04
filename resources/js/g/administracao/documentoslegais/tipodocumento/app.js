import { createApp } from 'vue'
import { registerGlobals } from '../../../../registerGlobals'
import tipodocumento from '../../../../components/administracao/documentoslegais/tipodocumento'

const app = createApp({
    data() {
        return {}
    },
    components: {
        tipodocumento
    }
})

registerGlobals(app)
app.mount('#app')
