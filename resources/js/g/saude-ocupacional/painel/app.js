import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import PainelSaudeOcupacional from '../../../components/saude-ocupacional/painel/PainelSaudeOcupacional'

const app = createApp({
    components: {
        PainelSaudeOcupacional
    }
})

registerGlobals(app)
app.mount('#app')
