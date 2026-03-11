import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import CartaOfertaTemplate from '../../../components/administracao/cartaoferta/CartaOfertaTemplate'

const app = createApp({
    data() {
        return {}
    },
    components: {
        CartaOfertaTemplate
    },
    template: '<CartaOfertaTemplate />'
})

registerGlobals(app)
app.mount('#app')
