import Select2 from "../../../components/Select2/Select2";
import configselect2 from "../../../components/Select2/mixSelec2";
import ExportacaoMixin from "../../../mixins/Exportacoes";

const app = new Vue({
    el: "#app",
    mixins: [configselect2,ExportacaoMixin],
    components: {
        Select2,
    },
    data: {
        preload: false,
        preloadExportacao: false,
        atualizado: false,
        AUTENTICADO,
        hash: `mastertag_${parseInt((Math.random() * 999999))}`,
        URL_ADMIN,
        lista: [],
        centros_de_custo: [],
        urlExportacao: `${URL_ADMIN}/relatorios/efetivo/export-excel`,
        urlPdf: `${URL_ADMIN}/relatorios/efetivo/pdf`,
        csrf: CSRF_token,

        controle: {
            carregando: false,
            dados: {
                pages: 50,
                campoCentrosDeCusto: "",
            }
        }
    },
    computed: {
        paramsExport() {
            return {
                campoCentrosDeCusto: this.controle.dados.campoCentrosDeCusto
            }
        },

        total() {
            return this.lista.reduce((a, b) => {
                return a + b.admissao.length;
            }, 0);
        }
    },
    mounted() {
        this.atualizar();
    },
    methods: {
        gerarPdf() {
            this.preloadAjax = true;
            axios.post(`${URL_ADMIN}/relatorios/efetivo/pdf`).then((response) => {
                this.preloadAjax = false;
                window.open(response.data.url, "_blank");
            }).catch((error) => (this.preloadAjax = false));
        },
        carregou(dados) {
            this.lista = dados.itens;
            this.centros_de_custo = dados.centros_de_custo;
            this.controle.carregando = false;
        },
        carregando() {
            this.controle.carregando = true;
        },
        atualizar() {
            this.$refs.componente.atual = 1;
            this.$refs.componente.buscar();
        }
    }
});
