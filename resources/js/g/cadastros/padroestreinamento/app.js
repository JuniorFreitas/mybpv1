import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import PadroesTreinamentoCadastro from '../../../components/cadastros/padroestreinamento/PadroesTreinamentoCadastro'

const app = createApp({
    components: {
        PadroesTreinamentoCadastro
    }
})

registerGlobals(app)
app.mount('#app')
