import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import Mobilizacao from '../../../components/planejamento/mobilizacao'

const app = createApp({
    data() {
        return {}
    },
    components: {
        Mobilizacao
    }
})

registerGlobals(app)
app.mount('#app')
