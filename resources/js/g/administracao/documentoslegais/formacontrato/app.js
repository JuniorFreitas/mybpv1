import { createApp } from 'vue'
import { registerGlobals } from '../../../../registerGlobals'
import formacontrato from '../../../../components/administracao/documentoslegais/formacontrato'

const app = createApp({
    data() {
        return {}
    },
    components: {
        formacontrato
    }
})

registerGlobals(app)
app.mount('#app')
