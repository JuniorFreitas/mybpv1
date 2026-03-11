import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import RequisicaoVagaCamposCustom from '../../../components/planejamento/requisicao-vagas/RequisicaoVagaCamposCustom.vue'

const app = createApp({
    data() {
        return {}
    },
    components: {
        RequisicaoVagaCamposCustom
    }
})

registerGlobals(app)
app.mount('#app')
