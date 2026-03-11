import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import Areas from '../../../components/cadastros/areas/Areas'

const app = createApp({
    components: {
        Areas
    },
    data() {
        return {}
    }
})

registerGlobals(app)
app.mount('#app')
