import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import NotificacoesConfig from '../../../components/configuracoes/notificacoes/NotificacoesConfig.vue'

const app = createApp({
    components: {
        NotificacoesConfig,
    },
})

registerGlobals(app)
app.mount('#app')
