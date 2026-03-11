import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import Ferias from '../../../components/relatorios/ferias'

const app = createApp({
    data() {
        return {}
    },
    components: {
        Ferias
    }
})

registerGlobals(app)
app.mount('#app')
