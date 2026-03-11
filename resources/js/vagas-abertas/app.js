import { createApp } from 'vue'
import { registerGlobals } from '../registerGlobals'
import VagasAbertas from '../../js/components/vagas-abertas/VagasAbertas'

const app = createApp({
    data() {
        return {}
    },
    components: {
        VagasAbertas
    }
})

registerGlobals(app)
app.mount('#app')
