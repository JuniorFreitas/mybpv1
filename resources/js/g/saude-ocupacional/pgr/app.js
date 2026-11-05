import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import PgrRiscos from '../../../components/saude-ocupacional/pgr/PgrRiscos'

const app = createApp({
    components: {
        PgrRiscos
    }
})

registerGlobals(app)
app.mount('#app')
