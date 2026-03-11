import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import EmpresaExame from '../../../components/cadastros/empresaexame/EmpresaExame'

const app = createApp({
    data() {
        return {}
    },
    components: {
        EmpresaExame
    }
})

registerGlobals(app)
app.mount('#app')
