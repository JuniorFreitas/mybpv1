import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import Usuarios from '../../../components/usuarios/Usuarios.vue'

const app = createApp({
    components: { Usuarios }
})

registerGlobals(app)
app.mount('#app')
