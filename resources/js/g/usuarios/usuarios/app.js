import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import telefone from '../../../components/Telefones'
import WhatsappPreferenciasForm from '../../../components/WhatsappPreferenciasForm.vue'
import Usuarios from '../../../components/usuarios/Usuarios.vue'

const app = createApp({
    components: { Usuarios }
})

registerGlobals(app)
app.component('telefone', telefone)
app.component('whatsapp-preferencias-form', WhatsappPreferenciasForm)
app.mount('#app')
