import fluxoCaixa from "../../../components/financeiro/FluxoCaixa"

const app = new Vue({
    el: '#app',
    components: {
        'fluxo-caixa': fluxoCaixa,
    },
    data: {
        preload: false,
        conta_id:null,
        numero_conta:''

    },
    mounted() {

    },
    methods: {
    }


});

