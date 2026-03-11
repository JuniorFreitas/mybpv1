import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import MedidasAdministrativas from '../../../components/relatorios/medidasadministrativas/MedidasAdministrativas'

const app = createApp({
    data() {
        return {}
    },
    components: {
        MedidasAdministrativas
    }
})

registerGlobals(app)
app.mount('#app')
