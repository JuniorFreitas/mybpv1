import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import ImportacaoAdmissoes from '../../../components/admissao/import/ImportacaoAdmissoes.vue'

const app = createApp({
    components: {
        ImportacaoAdmissoes
    }
})

registerGlobals(app)
app.mount('#app')
