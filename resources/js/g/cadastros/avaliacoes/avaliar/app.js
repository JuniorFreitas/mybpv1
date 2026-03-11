import { createApp } from 'vue'
import { registerGlobals } from '../../../../registerGlobals'
import avaliar from '../../../../components/cadastros/avaliacoes/avaliar'

const app = createApp({
    components: {
        avaliar
    },
    data() {
        return {}
    }
})

registerGlobals(app)
app.mount('#app')
