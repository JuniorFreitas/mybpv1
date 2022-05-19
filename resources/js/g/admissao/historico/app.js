import Dossie from "../../../components/admissao/historico/Dossie";
import MedidasAdministrativas from "../../../components/admissao/MedidasAdministrativas";
import FormularioNoventaDias from "../../../components/admissao/historico/FormularioNoventaDias";
import AvaliacaoAnual from "../../../components/admissao/historico/AvaliacaoAnual";
import Ferias from "../../../components/admissao/historico/Ferias";
import Beneficio from "../../../components/admissao/historico/Beneficio";
import Cih from "../../../components/admissao/historico/CIH";
import Promocao from "../../../components/admissao/historico/Promocao";
import Metas from "../../../components/admissao/historico/Meta";
import FeedbackHistorico from "../../../components/admissao/historico/FeedbackHistorico";

const app = new Vue({
    el: "#app",
    name: "Historico",
    components: {
        Dossie,
        MedidasAdministrativas,
        FeedbackHistorico,
        FormularioNoventaDias,
        AvaliacaoAnual,
        Ferias,
        Beneficio,
        Cih,
        Promocao,
        Metas
    },
    data: {
        tituloJanela: "Histórico",
        preload: false,
        cadastrando: false,

        hash: `mastertag_${parseInt((Math.random() * 999999))}`,

        abas:{
            abrirDossie: false,
            abrirMedidas: false,
            abrirFeedbackHistorico: false,
            abrirFormularioNoventa: false,
            abrirAvaliacaoAnual: false,
            abrirFerias: false,
            abrirBeneficio: false,
            abrirCih: false,
            abrirPromocao: false,
            abrirMetas: false,
        },
        abasDefault: null,

        form: {
            feedback_id: 0,
            curriculo_id: 0,
            medidas_administrativas: [],
            medidas_administrativasDelete: []
        },

        formDefault: null,

        cliente_id: "",

        lista: [],
        cargos: [],

        controle: {
            carregando: false,
            dados: {
                caminho_autocomplete: `autocomplete/todas-vagas-ativas`,
                pages: 20,
                campoBusca: "",
                campoCargo: ""
            }
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form); //copia
        this.abasDefault = _.cloneDeep(this.abas) //copia
        this.abas.abrirDossie = true
        this.atualizar();
    },
    methods: {
        trocaAba(aba) {
            this.abas = _.cloneDeep(this.abasDefault) //copia
            this.abas[aba] = true
        },
        abrirHistorico(obj) {
            this.tituloJanela = `#${obj.id} - Histórico: ${obj.curriculo.nome}`;
            this.form = _.cloneDeep(obj);
            this.form.feedback_id = obj.id;
            this.form.curriculo_id = obj.curriculo_id;

            setTimeout(() => {
                this.trocaAba('abrirDossie')
            }, 200);
            $("#nav-dossie-tab").tab("show");

        },

        //GERAL
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = "";
                this.controle.dados.autocomplete_label = "";
                this.controle.dados.campoVaga = "";
            }
        },

        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id;
            this.controle.dados.autocomplete_label = obj.label;
            this.controle.dados.autocomplete_label_anterior = obj.label;
        },

        resetaCampoCliente() {
            if (this.controle.dados.autocomplete_label_cliente_anterior !== this.controle.dados.autocomplete_label_cliente) {
                this.controle.dados.autocomplete_label_cliente_anterior = "";
                this.controle.dados.autocomplete_label_cliente = "";
                this.controle.dados.campoCliente = "";
            }
        },

        carregou(dados) {
            this.lista = dados.itens;
            this.cargos = dados.cargos;
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
