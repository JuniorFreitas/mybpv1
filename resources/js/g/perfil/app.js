import { createApp } from 'vue'
import { registerGlobals } from '../../registerGlobals'
import Perfil from '../../components/perfil/Perfil'

const app = createApp({
    data() {
        return {}
    },
    components: {
        Perfil
    }
})

registerGlobals(app)
app.mount('#app')
