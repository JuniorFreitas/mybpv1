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
        atualizando: false,

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

                autocomplete_vaga_label_anterior: '',
                autocomplete_vaga_label: '',

                pages: 50,
                campoBusca: '',
                status: '',
                curriculo_id: '',
                vaga_projeto_id: '',
                vagas_abertas_id: '',
                order: 'nome',
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
        resetaCampoVaga() {
            if (this.controle.dados.autocomplete_vaga_label_anterior !== this.controle.dados.autocomplete_vaga_label) {
                this.controle.dados.autocomplete_vaga_label_anterior = '';
                this.controle.dados.autocomplete_vaga_label = '';
                this.controle.dados.vagas_abertas_id = '';
            }
        },

        selecionaVaga(obj) {
            this.controle.dados.vagas_abertas_id = obj.id;
            this.controle.dados.autocomplete_vaga_label = obj.label;
            this.controle.dados.autocomplete_vaga_label_anterior = obj.label;
        },

        async formVisualizar(obj) {
            this.objopen = null;
            this.abriupdf = false;
            this.objopen = obj;
            this.atualizanndo = false;
            this.$nextTick(() => {
                this.abriupdf = true;
            });
           await this.getIntegraMybp(obj.token);
        },

        carregouPdf() {
            this.preloadbotoes = false;
        },

        async responder(obj, resposta) {
            this.atualizanndo = true;
            this.preload = true;
            obj.resposta = resposta;

            await axios.put(`${this.urlDefault}/responder`, obj).then(response => {
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

        async getIntegraMybp(token) {
            let endpoint = '';
            switch (window.location.hostname) {
                case 'qa.mybp.com.br':
                    endpoint = `https://qasgi.bpse.com.br/api/carta-oferta/${token}/integramybp`;
                    break;
                case 'sistema.mybp.com.br':
                    endpoint = `https://sgi.bpse.com.br/api/carta-oferta/${token}/integramybp`;
                    break;
                default:
                    endpoint = `http://localhost:8884/api/carta-oferta/${token}/integramybp`;
                    break;
            }

            await axios.post(`${endpoint}/`,
                {},
                {
                    headers: {
                        'X-API-TOKEN': 'gTyF2ErmclLMRjzxBHo20OoXVqNhgnDKqCtQVRtsrfF1sOU4s6wK'
                    }
                }
            )
                .then(({data}) => {
                    this.objopen.integraMybp = data;
                })
                .catch(error => {
                    this.preload = false
                    mostraErro('', 'Erro ao integrar caso o erro persista, entre em contato com o suporte.');
                    return false;
                })
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
