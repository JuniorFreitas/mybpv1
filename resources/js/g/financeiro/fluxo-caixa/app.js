import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import fluxoCaixa from '../../../components/financeiro/FluxoCaixa'

const app = createApp({
    components: {
        'fluxo-caixa': fluxoCaixa
    },
    data() {
        return {
            preload: false,
            conta_id: null,
            numero_conta: ''
        }
    },
    mounted() {},
    methods: {}
})

registerGlobals(app)
app.mount('#app')
