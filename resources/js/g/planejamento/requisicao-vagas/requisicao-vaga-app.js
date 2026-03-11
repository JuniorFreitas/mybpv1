/**
 * Entry point: Requisição de Vagas como componente Vue.
 * A blade apenas renderiza <requisicao-vaga url-atualizar="..."> e este script monta o Vue.
 */
import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import RequisicaoVaga from '../../../components/planejamento/requisicao-vagas/RequisicaoVaga.vue'

const app = createApp({
    components: {
        RequisicaoVaga
    }
})

registerGlobals(app)
app.mount('#app')
