import endereco from "../../components/Endereco";
import upload from "../../components/Upload";
import telefone from "../../components/Telefones";


const app = new Vue({
    el: '#app',
    components: {
        endereco,
        upload,
        telefone,
    },
    data: {
        tituloJanela: 'Curriculo',

        anexoUploadAndamento: false,
        hash: `mastertag_${parseInt((Math.random() * 999999))}`,
        todos_municipios: `autocomplete/todos-municipios`,


        form: {
            preload: false,
            editando: false,
            atualizado: false,


            feedback: {
                id: '',

                vaga_id: '',
                interesse: true,

                autocomplete_label_vaga_modal: '',
                autocomplete_label_vaga_modal_anterior: '',

                curriculo: {
                    nome: '',
                    rg: '',
                    orgao_expeditor: '',
                    nascimento: '',
                    email: '',
                    logradouro: '',
                    complemento: '',
                    bairro: '',
                    municipio: '',
                    uf: '',
                    cep: '',
                    municipio_id: '',

                    filiacao_pai: '',
                    filiacao_mae: '',

                    autocomplete_label_municipio_modal: '',
                    autocomplete_label_municipio_modal_anterior: '',

                    foto_tres: [],
                    foto_tresDel: []
                },

                admissao:{
                    funcao: '',
                }
            },

        },

        URL_ADMIN,
        disabled: true,
        selecionados: [],
        selecionaTudo: false,

        lista: [],

        controle: {
            carregando: false,
            dados: {
                caminho_autocomplete: `autocomplete/todas-vagas-ativas`,
                autocomplete_label_anterior: '',
                autocomplete_label: '',
                pages: 20,
                campoBusca: '',
                campoVaga: '',
                campoLido: '',
                campoFiltro: '',
                campoPcd: '',
                campoUf: ''
            },
        },
    },
    computed: {
        tudoMarcado() {
            let totalItens = this.lista.length;
            let totalEncontrado = 0;

            if (totalItens === 0) {
                return false;
            }

            this.lista.forEach(item => {
                let id = item.curriculo_id;
                if (this.selecionados.indexOf(id) >= 0) {
                    totalEncontrado++;
                    //faz nada
                } else {
                    return false;
                }
            });
            let resultado = totalItens === totalEncontrado;
            this.selecionaTudo = resultado;
            return resultado;
        }
    },
    mounted() {
        this.atualizar();
        this.listaVagas();
    },
    methods: {
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo;
            if (this.selecionaTudo) {
                this.lista.map(item => {
                    let id = item.curriculo_id;
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id)
                    }
                });
            } else {
                this.lista.map(item => {
                    let id = item.curriculo_id;
                    let index = this.selecionados.indexOf(id);
                    if (index >= 0) {
                        this.selecionados.splice(index, 1)
                    }
                });
            }
        },

        selecionaMunicipioModal(obj) {
            this.form.feedback.curriculo.municipio_id = obj.id;
            this.form.feedback.curriculo.autocomplete_label_municipio_modal = obj.label;
            this.form.feedback.curriculo.autocomplete_label_municipio_modal_anterior = obj.label;
        },

        resetaCampoMunicipioModal() {
            if (this.form.feedback.curriculo.autocomplete_label_municipio_modal_anterior !== this.form.feedback.curriculo.autocomplete_label_municipio_modal) {
                this.form.feedback.curriculo.autocomplete_label_municipio_modal_anterior = '';
                this.form.feedback.curriculo.autocomplete_label_municipio_modal = '';
                this.form.feedback.curriculo.municipio_id = '';

                setTimeout(() => {
                    if (this.form.feedback.curriculo.municipio_id === '') {
                        valida_campo_vazio($('#mun_' + this.hash), 1);
                        $('#janelaAdmissaoAvulsa #mun_' + this.hash).focus().trigger('blur');
                        mostraErro('Erro', 'O Campo Município não pode ficar vazio');
                    }
                }, 100);
            }
        },

        selecionaVagaModal(obj) {
            this.form.feedback.vaga_id = obj.id;
            this.form.feedback.autocomplete_label_vaga_modal = obj.label;
            this.form.feedback.autocomplete_label_vaga_modal_anterior = obj.label;
        },
        resetaCampoVagaModal() {
            if (this.form.feedback.autocomplete_label_vaga_modal_anterior !== this.form.feedback.autocomplete_label_vaga_modal) {
                this.form.feedback.autocomplete_label_vaga_modal_anterior = '';
                this.form.feedback.autocomplete_label_vaga_modal = '';
                this.form.feedback.vaga_id = '';
                setTimeout(() => {
                    if (this.form.feedback.vaga_id === '') {
                        mostraErro('Erro', 'O Campo Vaga não pode ficar vazio');
                    }
                }, 100);
            }
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


        formAlterar(feedback_id) {
            this.form.editando = true;
            this.form.atualizado = false;
            this.form.preload = true;

            Object.assign(this.form, this.formDefault);

            axios.get(`${URL_ADMIN}/portaria/${feedback_id}`)
                .then(response => {
                    let data = response.data;
                    Object.assign(this.form, data);
                    this.form.admissao.funcao = this.form.admissao ? this.form.admissao.funcao : '';
                    this.form.preload = false;
                })
                .catch(error => {
                    this.form.preload = false;
                })
        },

        salvar() {
            formReset();

            if (this.form.feedback.vaga_id === '') {
                valida_campo_vazio($('#vaga_' + this.hash), 1);
                $('#janelaPortaria #vaga_' + this.hash).focus().trigger('blur');
                mostraErro('', 'O campo vaga não pode ficar vazio');
                return false;
            }

            if (this.form.feedback.curriculo.municipio_id === '') {
                valida_campo_vazio($('#mun_' + this.hash), 1);
                $('#janelaPortaria #mun_' + this.hash).focus().trigger('blur');
                mostraErro('', 'O Campo Cidade não pode ficar vazio');
                return false;
            }

            $('#janelaPortaria :input:visible').trigger('blur');
            if ($('#janelaPortaria :input:visible.is-invalid').length) {
                $('#janelaPortaria').animate({
                    scrollTop: $($('.is-invalid')[0]).offset().top
                }, 800, function () {
                });
                mostraErro('', 'Verifique os erros')
                return false;
            }

            this.form.preload = true;

            axios.put(`${URL_ADMIN}/portaria/${this.form.feedback.id}`, this.form)
                .then(response => {
                    if (response.status === 201) {
                        this.form.preload = false;
                        this.form.editando = false;
                        this.form.atualizado = true;
                        this.atualizar();
                    }
                }).catch(error => (this.form.preload = false));

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
            this.selecionaTudo = this.tudoMarcado;
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
