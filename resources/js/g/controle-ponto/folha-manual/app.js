import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import FolhaManual from '../../../components/controle-ponto/folha-manual/FolhaManual.vue'

const app = createApp({
    data() {
        return {}
    },
    components: {
        FolhaManual
    }
})

registerGlobals(app)
app.mount('#app')
