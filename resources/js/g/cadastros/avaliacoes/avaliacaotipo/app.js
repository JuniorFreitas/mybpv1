import { createApp } from 'vue'
import { registerGlobals } from '../../../../registerGlobals'
import avaliacaotipo from '../../../../components/cadastros/avaliacoes/avaliacaotipo'

const app = createApp({
    components: {
        avaliacaotipo
    },
    data() {
        return {}
    }
})

registerGlobals(app)
app.mount('#app')
