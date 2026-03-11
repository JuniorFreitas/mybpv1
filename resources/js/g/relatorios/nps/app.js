import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import NpsRelatorio from '../../../components/relatorios/nps/NpsRelatorio.vue'

const app = createApp({
    data() {
        return {}
    },
    components: {
        NpsRelatorio
    }
})

registerGlobals(app)
app.mount('#app')
