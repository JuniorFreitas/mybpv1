import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import PcmsoProgramas from '../../../components/saude-ocupacional/pcmso/PcmsoProgramas'

const app = createApp({
    components: {
        PcmsoProgramas
    }
})

registerGlobals(app)
app.mount('#app')
