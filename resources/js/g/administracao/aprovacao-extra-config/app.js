import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import AprovacaoExtraConfig from '../../../components/administracao/aprovacao-extra-config/AprovacaoExtraConfig'

const app = createApp({
    data() {
        return {}
    },
    components: {
        AprovacaoExtraConfig
    }
})

registerGlobals(app)
app.mount('#app')
