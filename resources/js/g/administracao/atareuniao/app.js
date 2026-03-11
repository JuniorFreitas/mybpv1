import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import AtaReuniao from '../../../components/administracao/atareuniao/AtaReuniao'

const app = createApp({
    data() {
        return {}
    },
    components: {
        AtaReuniao
    }
})

registerGlobals(app)
app.mount('#app')
