import upload from '../../../../components/Upload';
import validacoes from "../../../../mixins/Validacoes";
import visualizadorPdf from "../../../../components/visualizadorPdf.vue";

const app = new Vue({
    el: '#app',
    mixins: [validacoes],
    components: {
        upload,
        visualizadorPdf
    },
    data: {
        tituloJanela: 'Carta Oferta',
        preload: false,
        atualizanndo: false,

        anexoUploadAndamento: false,
        urlAnexoUpload: `${URL_SITE}/documentos/uploadAnexos`,

        hash: `${parseInt((Math.random() * 999999))}`,

        todos_municipios: `autocomplete/todos-municipios`,
        preloadbotoes: true,

        URL_ADMIN,
        objopen: null,
        abriupdf: false,

        lista: [],
        lista_status: [],

        vagas: [],
        areasEtiquetas: [],

        controle: {
            carregando: false,
            dados: {
                caminho_autocomplete: `autocomplete/todas-vagas-ativas`,
                autocomplete_label_anterior: '',
                autocomplete_label: '',
                pages: 50,
                status: '',
                curriculo_id: '',
                vaga_projeto_id: '',
                vagas_abertas_id: '',
            },
        },
    },
    computed: {
        urlDefault() {
            return `${URL_ADMIN}/admissao/documentos/carta-oferta`;
        },
        urlPaginacao() {
            return `${this.urlDefault}/atualizar`;
        },
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.atualizar();
        this.listaVagas();
    },
    methods: {

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

        formVisualizar(obj) {
            this.objopen = null;
            this.abriupdf = false;
            this.objopen = obj;
            this.atualizanndo = false;
            this.$nextTick(() => {
                this.abriupdf = true;
            });
        },

        carregouPdf() {
            this.preloadbotoes = false;
        },

        responder(obj, resposta) {
            this.atualizanndo = true;
            this.preload = true;
            obj.resposta = resposta;
            axios.put(`${this.urlDefault}/responder`,obj).then(response => {
                if (resposta === 'Recusado pelo RH') {
                    $('#janelaRecusar').modal('hide');
                }
                $('#janelaVisualizar').modal('hide');
                mostraSucesso('', 'Resposta computada com sucesso');
                this.preload = false;
                this.atualizando = false;
                this.atualizar();
            }).catch(error => (this.preload = false));
        },

        listaVagas() {
            this.preload = true;
            $.get(`${URL_PUBLICO}/lista-vagas`)
                .done((data) => {
                    this.preload = false;
                    this.vagas = data.vagas;
                })
                .fail((data) => {
                    this.preload = false;
                });
        },

        carregou(dados) {
            this.lista = dados.itens;
            this.lista_status = dados.lista_status;
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
