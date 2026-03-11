import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import EmpresaTreinamento from '../../../components/cadastros/empresatreinamento/EmpresaTreinamento'

const app = createApp({
    data() {
        return {}
    },
    components: {
        EmpresaTreinamento
    }
})

registerGlobals(app)
app.mount('#app')
