import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import ControleUsuarios from '../../../components/relatorios/controleusuarios/ControleUsuarios'

const app = createApp({
    data() {
        return {}
    },
    components: {
        ControleUsuarios
    }
})

registerGlobals(app)
app.mount('#app')
