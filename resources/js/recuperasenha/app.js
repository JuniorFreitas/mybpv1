import { createApp } from 'vue'
import { registerGlobals } from '../registerGlobals'
import RecuperaSenha from '../../js/components/recuperaSenha'

const app = createApp({
    data() {
        return {}
    },
    components: {
        RecuperaSenha
    }
})

registerGlobals(app)
app.mount('#app')
