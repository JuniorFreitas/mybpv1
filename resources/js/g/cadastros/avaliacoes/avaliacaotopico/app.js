import { createApp } from 'vue'
import { registerGlobals } from '../../../../registerGlobals'
import avaliacaotopico from '../../../../components/cadastros/avaliacoes/avaliacaotopico'

const app = createApp({
    components: {
        avaliacaotopico
    },
    data() {
        return {}
    }
})

registerGlobals(app)
app.mount('#app')
