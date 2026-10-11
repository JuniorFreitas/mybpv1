import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import ImportacaoTreinamentos from '../../../components/treinamentos/import/ImportacaoTreinamentos.vue'

const app = createApp({
    components: {
        ImportacaoTreinamentos
    }
})

registerGlobals(app)
app.mount('#app')
