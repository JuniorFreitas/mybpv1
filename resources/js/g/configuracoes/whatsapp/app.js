import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import WhatsappConfig from '../../../components/configuracoes/whatsapp/WhatsappConfig'

const app = createApp({
    components: {
        WhatsappConfig,
    },
})

registerGlobals(app)
app.mount('#app')
