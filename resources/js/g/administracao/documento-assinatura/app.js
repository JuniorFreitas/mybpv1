import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import DocumentoAssinatura from '../../../components/administracao/documentoassinatura/DocumentoAssinatura.vue'

const app = createApp({
    data() {
        return {}
    },
    components: {
        DocumentoAssinatura
    }
})

registerGlobals(app)
app.mount('#app')
