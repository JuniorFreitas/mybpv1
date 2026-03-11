import { createApp } from 'vue'
import { registerGlobals } from '../../../../registerGlobals'
import avaliadortipo from '../../../../components/cadastros/avaliacoes/avaliadortipo'

const app = createApp({
    components: {
        avaliadortipo
    },
    data() {
        return {}
    }
})

registerGlobals(app)
app.mount('#app')
