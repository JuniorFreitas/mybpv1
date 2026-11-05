import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import DocumentosSst from '../../../components/saude-ocupacional/documentos/DocumentosSst'

const app = createApp({
    components: {
        DocumentosSst
    }
})

registerGlobals(app)
app.mount('#app')
