import preload from '../../../components/preload';
import datepicker from '../../../components/DatePicker';
import autoComplete from "../../../components/AutoComplete";
import escala from "../../../components/controle-ponto/Escala";
import formataNome from "../../../filters/formataNomeUser";


const app = new Vue({
    el: '#app',
    components: {
        preload,
        datepicker,
        autoComplete,
        escala
    },
    filters: {
        formataNome
    },
    data: {
        URL_ADMIN,
        EMPRESA_ID: null,
        preload: true,
        formBusca: {
            preload: false,
            funcionarioNome: '',
            status: 'admitidos',
            escala_id: '',
        },
        lista: [],
        formPonto: {
            intervalo: `${moment().startOf('month').format('DD/MM/YYYY')} até ${moment().endOf('month').format('DD/MM/YYYY')}`,
            preload: true,
            id: null,
            preloadFrequencia: false,
            pontos: [],
        },
        todas_escalas: [],
        controle_ponto_adm: false,
        calendario: [],

        formPontoDefault: null,
        OCORRENCIA_FALTA: null,
        USER_ID: null,
    },
    mounted() {

        String.prototype.capitalize = function () {
            return this.charAt(0).toUpperCase() + this.substr(1);
        }
        this.formPontoDefault = _.cloneDeep(this.formPonto);
        this.porPagina = this.porPaginaPadrao;
        this.atualizar();

    },
    computed: {
        quantidadeFaltas() {
            return this.formPonto.pontos.filter(ponto => ponto.ocorrencia_id === this.OCORRENCIA_FALTA).length;
        },
        totalHorasNormais() {
            let total = 0;
            this.formPonto.pontos.filter(ponto => ponto.periodos_em_aberto.length === 0).forEach(ponto => {
                total += ponto.duracao_normal;
            });
            return total;
        },
        totalHorasNoturnas() {
            let total = 0;
            this.formPonto.pontos.filter(ponto => ponto.periodos_em_aberto.length === 0).forEach(ponto => {
                total += ponto.duracao_noturna;
            });
            return total;
        },
        totalHorasExtra() {
            let total = 0;
            this.formPonto.pontos.filter(ponto => ponto.periodos_em_aberto.length === 0).forEach(ponto => {
                total += ponto.duracao_extra > 0 ? ponto.duracao_extra : 0;
            });
            return total;
        },
        totalHorasNegativas() {
            let total = 0;
            this.formPonto.pontos.filter(ponto => ponto.periodos_em_aberto.length === 0).forEach(ponto => {
                total += ponto.duracao_extra < 0 ? Math.abs(ponto.duracao_extra) : 0;
            });
            return total;
        },
        saldoHoras() {
            return (this.totalHorasExtra + this.totalHorasNoturnas) - this.totalHorasNegativas;
        },
        urlImprimir() {
            return `${URL_ADMIN}/controle-ponto/folha-ponto/${this.formPonto.id}/imprimir`
        }
    },
    methods: {
        atualizar() {
            this.$refs.paginacao.atual = 1;
            this.$refs.paginacao.buscar();
        },
        carregou(dados) {
            this.lista = dados.itens;
            this.todas_escalas = dados.todas_escalas;
            this.controle_ponto_adm = dados.controle_ponto_adm;
            this.formBusca.preload = false;
        },
        carregando() {
            this.formBusca.preload = true;
        },
        formataTempo(value) {
            if (value < 10) {
                return '0' + value;
            }
            return value;
        },
        formataHoras(quantidade_minutos) {
            if (quantidade_minutos === 0) {
                return `00h:00m`;
            }
            let agora = moment();
            let depois = moment();
            depois.add(Math.abs(quantidade_minutos), 'minutes');
            let duration = moment.duration(depois.diff(agora));
            return `${parseInt(duration.asHours())}h:${this.formataTempo(duration.minutes())}m`;
        },
        //Molda folha de ponto -----------------------

        verDetalhes(id) {
            this.formPonto = _.cloneDeep(this.formPontoDefault);
            this.formPonto.preload = true;
            this.USER_ID = id;
            axios.get(`${URL_ADMIN}/controle-ponto/folha-ponto/${id}/editar`)
                .then(response => {
                    Object.assign(this.formPonto, response.data);
                    this.formPonto.preload = false;
                    this.buscarFrequencia();
                }).catch(error => {
                this.formPonto.preload = false;
            });
        },

        buscarFrequencia() {
            this.formPonto.preloadFrequencia = true;
            axios.post(`${URL_ADMIN}/controle-ponto/folha-ponto/${this.USER_ID}/frequencia`, {intervalo: this.formPonto.intervalo})
                .then(({data}) => {
                    this.formPonto.preloadFrequencia = false;
                    this.formPonto.pontos = data.pontos
                    this.OCORRENCIA_FALTA = data.ocorrencia_falta
                    this.calendario = data.calendario
                }).catch(error => {
                this.formPonto.preloadFrequencia = false;
            });
        },


    }
});
