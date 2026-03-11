import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import SegmentosTreinamentoCadastro from '../../../components/cadastros/segmentostreinamento/SegmentosTreinamentoCadastro'

const app = createApp({
    components: {
        SegmentosTreinamentoCadastro
    }
})

registerGlobals(app)
app.mount('#app')
