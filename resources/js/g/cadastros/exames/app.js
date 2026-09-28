import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import ExamesAdminHub from '../../../components/cadastros/exames/ExamesAdminHub'

const app = createApp({
    data() {
        return {}
    },
    components: {
        ExamesAdminHub
    }
})

registerGlobals(app)
app.mount('#app')
