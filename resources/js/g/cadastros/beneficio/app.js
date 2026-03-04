import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import Beneficio from '../../../components/cadastros/beneficio/Beneficio'

const app = createApp({
    components: {
        Beneficio
    },
    data() {
        return {}
    }
})

registerGlobals(app)
app.mount('#app')
