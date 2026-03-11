import { createApp } from 'vue'
import { registerGlobals } from '../../registerGlobals'
import ControleExames from '../../components/controle-exames/ControleExames.vue'

const app = createApp({
    components: { ControleExames }
})

registerGlobals(app)
app.mount('#app')
