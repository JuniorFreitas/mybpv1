/**
 * Avaliação de Experiência (ex Relatório de Avaliação de 90 dias)
 * Monta o componente Vue que consome a API /g/relatorios/avaliacao-de-experiencia/dados
 */
import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import AvaliacaoExperiencia from '../../../components/relatorios/AvaliacaoExperiencia.vue'

const app = createApp({
    data() {
        return {}
    },
    components: {
        AvaliacaoExperiencia
    }
})

registerGlobals(app)
app.mount('#app')
