import { createApp } from 'vue'
import { registerGlobals } from '../../registerGlobals'
import weekly_report from '../../components/Weekly-report'

const app = createApp({
    components: {
        'weekly-report': weekly_report
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
