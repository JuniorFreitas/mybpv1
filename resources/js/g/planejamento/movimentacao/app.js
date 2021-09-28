import SolicitacaoDemissao from "../../../components/planejamento/movimentacao/SolicitacaoDemissao";
import SolicitacaoFerias from "../../../components/planejamento/movimentacao/SolicitacaoFerias";
import SolicitacaoAdmissao from "../../../components/planejamento/movimentacao/SolicitacaoAdmissao";
import SolicitacaoValorExtra from "../../../components/planejamento/movimentacao/SolicitacaoValorExtra";
import SolicitacaoMudaCargo from "../../../components/planejamento/movimentacao/SolicitacaoMudaCargo";
import SolicitacaoIntermitenteFixo from "../../../components/planejamento/movimentacao/SolicitacaoIntermitenteFixo";

const app = new Vue({
    el: '#app',
    components: {
        'solicitacao-demissao': SolicitacaoDemissao,
        'solicitacao-ferias': SolicitacaoFerias,
        'solicitacao-admissao': SolicitacaoAdmissao,
        'solicitacao-valor-extra': SolicitacaoValorExtra,
        'solicitacao-muda-cargo': SolicitacaoMudaCargo,
        'solicitacao-intermitente-fixo': SolicitacaoIntermitenteFixo,
    },
    data: {
        preload: false,
        cliente_id: '',

        demissao: false,
        ferias: false,
        admissao: false,

        abas: {
            demissao: false,
            ferias: false,
            admissao: false,
            valorextra: false,
            mudacargo: false,
            intermitente: false,
        },
        abasDefault: null

    },
    mounted() {
        this.usuarioAutenticado();
        this.abasDefault = _.cloneDeep(this.abas) //copia
        this.abas.demissao = true
    },
    methods: {
        trocaAba(aba) {
            this.abas = _.cloneDeep(this.abasDefault) //copia
            this.abas[aba] = true
        },
        usuarioAutenticado() {
            this.preload = true;
            axios.get(`${URL_ADMIN}/usuario/autenticado/`)
                .then(response => {
                    let data = response.data;
                    this.cliente_id = data.cliente_id;
                })
                .catch(error => {
                    this.preload = false;
                })
        },
    }
});

