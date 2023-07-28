import SolicitacaoDemissao from "../../../components/planejamento/movimentacao/SolicitacaoDemissao";
import SolicitacaoFerias from "../../../components/planejamento/movimentacao/SolicitacaoFerias";
import SolicitacaoAdmissao from "../../../components/planejamento/movimentacao/SolicitacaoAdmissao";
import SolicitacaoValorExtra from "../../../components/planejamento/movimentacao/SolicitacaoValorExtra";
import SolicitacaoMudaCargo from "../../../components/planejamento/movimentacao/SolicitacaoMudaCargo";
import SolicitacaoIntermitenteFixo from "../../../components/planejamento/movimentacao/SolicitacaoIntermitenteFixo";
import SolicitacaoTransferencia from "../../../components/planejamento/movimentacao/SolicitacaoTransferencia";

const app = new Vue({
    el: '#app',
    components: {
        'solicitacao-demissao': SolicitacaoDemissao,
        'solicitacao-ferias': SolicitacaoFerias,
        'solicitacao-admissao': SolicitacaoAdmissao,
        'solicitacao-valor-extra': SolicitacaoValorExtra,
        'solicitacao-muda-cargo': SolicitacaoMudaCargo,
        'solicitacao-intermitente-fixo': SolicitacaoIntermitenteFixo,
        'solicitacao-transferencia': SolicitacaoTransferencia,
    },
    data: {
        preload: false,
        cliente_id: '',

        abas: {
            demissao: false,
            ferias: false,
            admissao: false,
            valorextra: false,
            mudacargo: false,
            intermitente: false,
        },
        abasDefault: null,
        permissoes_abas: [],
        aba_ativa: '',

    },
    mounted() {
        this.usuarioAutenticado();
        this.abasDefault = _.cloneDeep(this.abas) //copia
        this.listaAbas();
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
        listaAbas() {
            this.preload = true;
            axios.get(`${URL_ADMIN}/planejamento/movimentacao/lista-abas`)
                .then(response => {
                    let dados = response.data.dados;
                    this.preload = false;
                    this.permissoes_abas = dados.permissoes_abas;
                    this.aba_ativa = dados.aba_ativa;
                    console.log(this.aba_ativa);
                    this.trocaAba(this.aba_ativa);
                })
                .catch(data => {
                    this.preload = false;
                });
        },
    }
});

