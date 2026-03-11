import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import Treinamento from '../../../components/relatorios/treinamento'

const app = createApp({
    data() {
        return {}
    },
    components: {
        Treinamento
    }
})

registerGlobals(app)
app.mount('#app')
