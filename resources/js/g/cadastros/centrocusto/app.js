import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import CentroCusto from '../../../components/cadastros/centrocusto/CentroCusto'

const app = createApp({
    components: {
        CentroCusto
    },
    data() {
        return {}
    }
})

registerGlobals(app)
app.mount('#app')
