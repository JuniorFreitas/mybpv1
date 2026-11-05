import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import MedicosResponsaveis from '../../../components/saude-ocupacional/medicos/MedicosResponsaveis'

const app = createApp({
    components: {
        MedicosResponsaveis
    }
})

registerGlobals(app)
app.mount('#app')
