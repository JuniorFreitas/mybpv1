import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import DocumentosPreadmissao from '../../../components/cadastros/documentospreadmissao/DocumentosPreadmissao'

const app = createApp({
    components: {
        DocumentosPreadmissao
    },
    data() {
        return {}
    }
})

registerGlobals(app)
app.mount('#app')
