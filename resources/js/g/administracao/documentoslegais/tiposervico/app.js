import { createApp } from 'vue'
import { registerGlobals } from '../../../../registerGlobals'
import tiposervico from '../../../../components/administracao/documentoslegais/tiposervico'

const app = createApp({
    data() {
        return {}
    },
    components: {
        tiposervico
    }
})

registerGlobals(app)
app.mount('#app')
