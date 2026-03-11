import { createApp } from 'vue'
import { registerGlobals } from '../../../../registerGlobals'
import avaliacao from '../../../../components/cadastros/avaliacoes/avaliacao'

const app = createApp({
    components: {
        avaliacao
    },
    data() {
        return {}
    }
})

registerGlobals(app)
app.mount('#app')
