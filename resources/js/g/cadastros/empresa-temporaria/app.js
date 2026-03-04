import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import EmpresaTemporaria from '../../../components/cadastros/empresatemporaria/EmpresaTemporaria'

const app = createApp({
    name: 'EmpresaTemporaria',
    data() {
        return {}
    },
    components: {
        EmpresaTemporaria
    }
})

registerGlobals(app)
app.mount('#app')
