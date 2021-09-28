import Dossie from '../../../components/admissao/historico/Dossie';
import MedidasAdministrativas from '../../../components/admissao/MedidasAdministrativas';
import FormularioNoventaDias from "../../../components/admissao/historico/FormularioNoventaDias";
import AvaliacaoAnual from "../../../components/admissao/historico/AvaliacaoAnual";
import Ferias from "../../../components/admissao/historico/Ferias";
import Beneficio from "../../../components/admissao/historico/Beneficio";
import Cih from "../../../components/admissao/historico/CIH";
import Promocao from "../../../components/admissao/historico/Promocao";
import Metas from "../../../components/admissao/historico/Meta";

const app = new Vue({
    el: '#app',
    components: {
        Dossie,
        MedidasAdministrativas,
        FormularioNoventaDias,
        AvaliacaoAnual,
        Ferias,
        Beneficio,
        Cih,
        Promocao,
        Metas,
    },
    data: {
        tituloJanela: 'Histórico',
        preload: false,
        cadastrando: false,
        abrirDossie: false,
        abrirMedidas: false,
        abrirFormularioNoventa: false,
        abrirAvaliacaoAnual: false,
        abrirFerias: false,
        abrirBeneficio: false,
        abrirCih: false,
        abrirPromocao: false,
        abrirMetas: false,
        hash: `mastertag_${parseInt((Math.random() * 999999))}`,

        form: {
            feedback_id: 0,
            medidas_administrativas: [],
            medidas_administrativasDelete: [],
        },

        formDefault: null,

        cliente_id: '',

        lista: [],

        controle: {
            carregando: false,
            dados: {
                caminho_autocomplete: `autocomplete/todas-vagas-ativas`,
                pages: 20,
            },
        },
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.atualizar();
    },
    methods: {
        abrirHistorico(obj) {
            console.log(obj.curriculo)
            this.tituloJanela = `#${obj.id} - Histórico: ${obj.curriculo.nome}`;
            this.abrirDossie = false;
            this.abrirMedidas = false;
            this.abrirFormularioNoventa = false;
            this.abrirAvaliacaoAnual = false;
            this.abrirFerias = false;
            this.abrirBeneficio = false;
            this.abrirCih = false;
            this.abrirPromocao = false;
            this.abrirMetas = false;
            this.form = _.cloneDeep(obj)
            this.form.feedback_id = obj.id;

            setTimeout(() => {
                this.abrirDossie = true;
            }, 200)
            $('#nav-dossie-tab').tab('show');

            // setTimeout(() => {
            //     this.abrirMedidas = true;
            // }, 200)
            // setTimeout(() => {
            //     this.abrirFormularioNoventa = true;
            // }, 200)
            // setTimeout(() => {
            //     this.abrirAvaliacaoAnual = true;
            // }, 200)
            // setTimeout(() => {
            //     this.abrirFerias = true;
            // }, 200)
            // setTimeout(() => {
            //     this.abrirBeneficio = true;
            // }, 200)
            // setTimeout(() => {
            //     this.abrirCih = true;
            // }, 200)
            // setTimeout(() => {
            //     this.abrirPromocao = true;
            // }, 200)
            // setTimeout(() => {
            //     this.abrirMetas = true;
            // }, 200)
        },

        //GERAL
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = '';
                this.controle.dados.autocomplete_label = '';
                this.controle.dados.campoVaga = '';
            }
        },

        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id;
            this.controle.dados.autocomplete_label = obj.label;
            this.controle.dados.autocomplete_label_anterior = obj.label;
        },

        resetaCampoCliente() {
            if (this.controle.dados.autocomplete_label_cliente_anterior !== this.controle.dados.autocomplete_label_cliente) {
                this.controle.dados.autocomplete_label_cliente_anterior = '';
                this.controle.dados.autocomplete_label_cliente = '';
                this.controle.dados.campoCliente = '';
            }
        },

        selecionaCliente(obj) {
            this.controle.dados.campoCliente = obj.id;
            this.controle.dados.autocomplete_label_cliente = obj.label;
            this.controle.dados.autocomplete_label_cliente_anterior = obj.label;
        },

        usuarioAutenticado() {
            this.controle.carregando = true;
            axios.get(`${URL_ADMIN}/usuario/autenticado/`)
                .then(response => {
                    let data = response.data;

                    this.cliente_id = data.cliente_id;

                    this.colunasTabela.cliente = this.cliente_id === 0;
                    this.controle.dados.campoCliente = this.cliente_id !== 0 ? this.cliente_id : this.controle.dados.campoCliente;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        carregou(dados) {
            this.lista = dados.itens;
            this.controle.carregando = false;
        },
        carregando() {
            this.controle.carregando = true;
        },
        atualizar() {
            this.$refs.componente.atual = 1;
            this.$refs.componente.buscar();
        },
    }
});
