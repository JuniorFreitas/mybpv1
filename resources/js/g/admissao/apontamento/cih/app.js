import { createApp } from 'vue'
import { registerGlobals } from '../../../../registerGlobals'
import Cih from '../../../../components/admissao/apontamento/CIH'
const app = createApp({
    components: {
        Cih
    }
})

registerGlobals(app)
app.mount('#app')
