import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import Instrutor from '../../../components/cadastros/instrutor/Instrutor'

const app = createApp({
    data() {
        return {}
    },
    components: {
        Instrutor
    }
})

registerGlobals(app)
app.mount('#app')
