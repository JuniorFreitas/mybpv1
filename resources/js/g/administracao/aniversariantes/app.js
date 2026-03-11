import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import Aniversariantes from '../../../components/administracao/aniversariantes/Aniversariantes'

const app = createApp({
    data() {
        return {}
    },
    components: {
        Aniversariantes
    }
})

registerGlobals(app)
app.mount('#app')
