import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import RegrasEmpresa from '../../../components/saude-ocupacional/regras/RegrasEmpresa'

const app = createApp({
    components: {
        RegrasEmpresa
    }
})

registerGlobals(app)
app.mount('#app')
