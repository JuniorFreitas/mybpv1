import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import DossieTipos from '../../../components/cadastros/dossietipos/DossieTipos'

const app = createApp({
    components: {
        DossieTipos
    },
    data() {
        return {}
    }
})

registerGlobals(app)
app.mount('#app')
