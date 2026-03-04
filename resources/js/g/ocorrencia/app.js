import { createApp } from 'vue'
import { registerGlobals } from '../../registerGlobals'
import Ocorrencia from '../../components/ocorrencia/Ocorrencia'

const app = createApp({
    data() {
        return {}
    },
    components: {
        Ocorrencia
    }
})

registerGlobals(app)
app.mount('#app')
