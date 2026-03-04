import { createApp } from 'vue'
import { registerGlobals } from '../../registerGlobals'
import cloud from '../../components/Cloud'

const app = createApp({
    components: {
        cloud
    },
    data() {
        return {
            itemAtual: ''
        }
    },

    methods: {
        atualizar(item) {
            this.itemAtual = item
            // setTimeout(()=>{
            //     this.$refs.cloud.atualizar();
            // },10)
        },
        openFolder(itemAtual) {
            this.atualizar(itemAtual)
        }
    }
})

registerGlobals(app)
app.mount('#app')
