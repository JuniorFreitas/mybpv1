import autocomplete from '../../../components/AutoComplete'
import configTinyMCE from '../../../components/configTinyMCE';
import Editor from '@tinymce/tinymce-vue';

const app = new Vue({
    el: '#app',
    components: {
        autocomplete,
        Editor,
    },
    data: {
        config: configTinyMCE,
        tituloJanela: 'Cadastrando Vaga',
        preloadAjax: false,
        editando: false,
        apagado: false,

        pages: 10,

        vagas_ativas: `autocomplete/todas-vagas-ativas`,
        todos_municipios: `autocomplete/todos-municipios`,

        hash: `mastertag_${parseInt((Math.random() * 999999))}`,

        form: {
            vaga_id: '',

            autocomplete_label_vaga_modal: '',
            autocomplete_label_vaga_modal_anterior: '',

            autocomplete_label_municipio_modal: '',
            autocomplete_label_municipio_modal_anterior: '',

            descricao: '',
            requerimentos: '',
            municipio_id: '',
            ativo: true,
        },

        formDefault: null,
        campoNome: null,

        cadastrado: false,
        atualizado: false,

        lista: [],

        controle: {
            carregando: false,
            dados: {
                campoBusca: "",
                campoStatus: "",
            },
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.atualizar();
        // this.listaVagas();
    },
    methods: {
        selecionaVagaModal(obj) {
            this.form.vaga_id = obj.id;
            this.form.autocomplete_label_vaga_modal = obj.label;
            this.form.autocomplete_label_vaga_modal_anterior = obj.label;
        },
        resetaCampoVagaModal() {
            if (this.form.autocomplete_label_vaga_modal_anterior !== this.form.autocomplete_label_vaga_modal) {
                this.form.autocomplete_label_vaga_modal_anterior = '';
                this.form.autocomplete_label_vaga_modal = '';
                this.form.vaga_id = '';

                setTimeout(() => {
                    if (this.form.vaga_id === '') {
                        valida_campo_vazio($('#' + this.hash), 1);
                        $('#janelaCadastrar #' + this.hash).focus().trigger('blur');
                        mostraErro('Erro', 'O Campo Vaga não pode ficar vazio');
                    }
                }, 100);
            }
        },

        selecionaMunicipioModal(obj) {
            this.form.municipio_id = obj.id;
            this.form.autocomplete_label_municipio_modal = obj.label;
            this.form.autocomplete_label_municipio_modal_anterior = obj.label;
        },
        resetaCampoMunicipioModal() {
            if (this.form.autocomplete_label_municipio_modal_anterior !== this.form.autocomplete_label_municipio_modal) {
                this.form.autocomplete_label_municipio_modal_anterior = '';
                this.form.autocomplete_label_municipio_modal = '';
                this.form.municipio_id = '';

                setTimeout(() => {
                    if (this.form.municipio_id === '') {
                        valida_campo_vazio($('#mun_' + this.hash), 1);
                        $('#janelaCadastrar #mun_' + this.hash).focus().trigger('blur');
                        mostraErro('Erro', 'O Campo Cidade não pode ficar vazio');
                    }
                }, 100);
            }
        },

        listaVagas() {
            this.preloadAjax = true;
            axios.get(`${URL_PUBLICO}/cadastro/lista-vagas`)
                .then(response => {
                    let data = response.data;
                    this.preloadAjax = false;
                    this.vagas = data.vagas;

                })
                .catch(error => {
                    this.preloadAjax = false;
                })
        },

        formNovo() {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;

            this.tituloJanela = "Cadastrando Vaga";

            formReset();
            setupCampo();

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = false;

        },
        cadastrar() {
            formReset();
            if (this.form.vaga_id === '') {
                valida_campo_vazio($('#' + this.hash), 1);
                $('#janelaCadastrar #' + this.hash).focus().trigger('blur');
                mostraErro('Erro', 'O campo vaga não pode ficar vazio');
                return false;
            }
            if (this.form.municipio_id === '') {
                valida_campo_vazio($('#mun_' + this.hash), 1);
                $('#janelaCadastrar #mun_' + this.hash).focus().trigger('blur');
                mostraErro('Erro', 'O Campo Cidade não pode ficar vazio');
                return false;
            }

            $('#janelaCadastrar :input:enabled').trigger('blur');

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }

            this.preloadAjax = true;
            axios.post(`${URL_ADMIN}/cadastro/vagas-abertas`, this.form)
                .then(response => {
                    if (response.status === 201) {
                        this.preloadAjax = false;
                        this.cadastrado = true;
                        this.atualizar();
                    }
                }).catch(error => (this.preloadAjax = false));
        },
        formAlterar(id) {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.tituloJanela = "Alterando Vaga";
            this.preloadAjax = true;
            formReset();

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = true;

            axios.get(`${URL_ADMIN}/cadastro/vagas-abertas/${id}/editar`)
                .then(response => {
                    Object.assign(this.form, response.data);
                    this.form.autocomplete_label_vaga_modal = response.data.vaga.nome;
                    this.form.autocomplete_label_vaga_modal_anterior = response.data.vaga.nome;

                    this.form.autocomplete_label_municipio_modal = response.data.municipio.nome + ' - ' + response.data.municipio.uf;
                    this.form.autocomplete_label_municipio_modal_anterior = response.data.municipio.nome + ' - ' + response.data.municipio.uf;
                    this.editando = true;
                    this.preloadAjax = false;
                    setupCampo();
                }).catch(
                error => (this.preloadAjax = false)
            );

        },

        alterar() {
            formReset();
            $('#janelaCadastrar :input:enabled').trigger('blur');

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }

            this.form._method = 'PUT';
            this.preloadAjax = true;

            axios.put(`${URL_ADMIN}/cadastro/vagas-abertas/${this.form.id}`, this.form).then(response => {
                this.preloadAjax = false;
                this.atualizado = true;
                this.atualizar();
            }).catch(error => (this.preloadAjax = false));

        },

        carregou(dados) {
            this.lista = dados;
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
