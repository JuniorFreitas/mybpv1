import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import ConformidadeAso from '../../../components/saude-ocupacional/conformidade/ConformidadeAso'

const app = createApp({
    components: {
        ConformidadeAso
    }
})

registerGlobals(app)
app.mount('#app')
