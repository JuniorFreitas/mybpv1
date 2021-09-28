import cloud from '../../components/Cloud';

const app = new Vue({
    el: '#app',
    components: {
        cloud,
    },
    data: {
        itemAtual: ''
    },

    methods: {
        atualizar(item) {
            this.itemAtual = item;
            // setTimeout(()=>{
            //     this.$refs.cloud.atualizar();
            // },10)

        },
        openFolder(itemAtual){
            this.atualizar(itemAtual)
        }
    }

});
