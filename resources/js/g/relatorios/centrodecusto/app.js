import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import Select2 from '../../../components/Select2/Select2'
import configselect2 from '../../../components/Select2/mixSelec2'
import ExportacaoMixin from '../../../mixins/Exportacoes'

const app = createApp({
    mixins: [configselect2, ExportacaoMixin],
    components: {
        Select2
    },
    data() {
        return {
            preload: false,
            preloadExportacao: false,
            atualizado: false,
            AUTENTICADO,
            hash: `mastertag_${parseInt(Math.random() * 999999)}`,
            URL_ADMIN,
            lista: [],
            centros_de_custo: [],
            urlExportacao: `${URL_ADMIN}/relatorios/centrodecusto/export-excel`,
            urlPdf: `${URL_ADMIN}/relatorios/centrodecusto/pdf`,
            csrf: CSRF_token,

            controle: {
                carregando: false,
                dados: {
                    pages: 50,
                    campoCentrosDeCusto: ''
                }
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
                return a + b.admissao.length
            }, 0)
        }
    },
    mounted() {
        this.atualizar()
    },
    methods: {
        gerarPdf() {
            this.preloadAjax = true
            axios
                .post(`${URL_ADMIN}/relatorios/centrodecusto/pdf`)
                .then((response) => {
                    this.preloadAjax = false
                    window.open(response.data.url, '_blank')
                })
                .catch((error) => (this.preloadAjax = false))
        },
        carregou(dados) {
            this.lista = dados.itens
            this.centros_de_custo = dados.centros_de_custo
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        }
    }
})

registerGlobals(app)
app.mount('#app')
