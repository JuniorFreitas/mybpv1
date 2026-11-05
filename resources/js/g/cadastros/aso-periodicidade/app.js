import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import AsoPeriodicidade from '../../../components/cadastros/aso-periodicidade/AsoPeriodicidade'

const app = createApp({
    components: {
        AsoPeriodicidade
    }
})

registerGlobals(app)
app.mount('#app')
