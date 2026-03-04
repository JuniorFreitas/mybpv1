import { createApp } from 'vue'
import { registerGlobals } from '../../registerGlobals'
import chat from '../../components/Chat'

const app = createApp({
    components: {
        chat
    },
    data() {
        return {
            preload: false
        }
    },
    mounted() {},
    methods: {}
})

registerGlobals(app)
app.mount('#app')
