import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import Departamento from '../../../components/cadastros/departamento/Departamento'

const app = createApp({
    data() {
        return {}
    },
    components: {
        Departamento
    }
})

registerGlobals(app)
app.mount('#app')
