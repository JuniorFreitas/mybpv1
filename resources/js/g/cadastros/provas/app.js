import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import Prova from '../../../components/cadastros/prova/cadProva'

const app = createApp({
    data() {
        return {}
    },
    components: {
        Prova
    }
})

registerGlobals(app)
app.mount('#app')
