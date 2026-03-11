import { createApp } from 'vue'
import { registerGlobals } from '../../../../registerGlobals'
import documentossma from '../../../../components/administracao/documentoslegais/documentossma'

const app = createApp({
    components: {
        documentossma
    }
})

registerGlobals(app)
app.mount('#app')
