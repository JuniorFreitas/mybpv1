import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import TipoCih from '../../../components/cadastros/tipocih'

const app = createApp({
    data() {
        return {}
    },
    components: {
        TipoCih
    }
})

registerGlobals(app)
app.mount('#app')
