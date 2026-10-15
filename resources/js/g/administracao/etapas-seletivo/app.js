import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import EtapasSeletivo from '../../../components/administracao/etapas-seletivo/EtapasSeletivo'

const app = createApp({
    components: {
        EtapasSeletivo
    }
})

registerGlobals(app)
app.mount('#app')
