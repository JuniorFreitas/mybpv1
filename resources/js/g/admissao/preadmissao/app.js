import upload from '../../../components/Upload';

const app = new Vue({
    el: '#app',
    components: {
        upload,
    },
    data: {
        tituloJanela: 'Pré-admissão',
        preload: false,
        editando: false,
        apagado: false,
        cadastrado: false,
        cadastrando: false,
        atualizado: false,
        visualizar: false,
        disabled: true,
        disabledInput: false,
        btnBuscar: false,

        cliente_id: 0,

        exibiFormulario: false,
        possuiCadastro: false,

        anexoUploadAndamento: false,
        urlAnexoUpload: `${URL_SITE}/documentos/uploadAnexos`,

        hash: `mastertag_${parseInt((Math.random() * 999999))}`,

        todos_municipios: `autocomplete/todos-municipios`,

        URL_ADMIN,
        selecionados: [],
        selecionaTudo: false,

        form:{
            id: '',
            nome: '',
            cpf: '',

            foto_tres: [], //FOTO 3X4
            foto_tresDel: [],

            anexos_cpf_rg: [],
            anexos_cpf_rgDel: [],

            comprovante_end: [],
            comprovante_endDel: [],

            ctps_frente: [],
            ctps_frenteDel: [],

            ctps_verso: [],
            ctps_versoDel: [],

            antecedentes: [],
            antecedentesDel: [],

            titulo_eleitor: [],
            titulo_eleitorDel: [],

            certificado_reservista: [],
            certificado_reservistaDel: [],

            pis_rescisao: [],
            pis_rescisaoDel: [],

            certificado_escolaridade: [],
            certificado_escolaridadeDel: [],

            conta_banco: [],
            conta_bancoDel: [],

            carta_sindicato: [],
            carta_sindicatoDel: [],

            carteira_vacina: [],
            carteira_vacinaDel: [],

            rgcpf_filho: [],
            rgcpf_filhoDel: [],

            cartao_vacina_filho: [],
            cartao_vacina_filhoDel: [],

            declaracao_escolar_filho: [],
            declaracao_escolar_filhoDel: [],
        },
        formDefault: null,

        lista: [],
        vagas: [],
        areasEtiquetas: [],

        controle: {
            carregando: false,
            dados: {
                caminho_autocomplete: `autocomplete/todas-vagas-ativas`,
                autocomplete_label_anterior: '',
                autocomplete_label: '',
                caminho_cliente_autocomplete: `autocomplete/todos-clientes-ativos`,
                autocomplete_label_cliente_anterior: '',
                autocomplete_label_cliente: '',
                pages: 20,
                cliente_custom: '',
                campoBusca: '',
                campoVaga: '',
                campoLido: '',
                campoFiltro: '',
                campoPcd: '',
                campoCliente: '',
                campoUf: 'MA'
            },
        },
    },
    computed: {
        comAdm() {
            return this.lista.filter(item => {
                return item.admissao;
            });
        },
        tudoMarcado() {
            let totalItens = this.comAdm.length;
            let totalEncontrado = 0;

            if (totalItens === 0) {
                return false;
            }

            this.comAdm.forEach(item => {
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
        this.formDefault = _.cloneDeep(this.form) //copia
        this.formAvulsaDefault = _.cloneDeep(this.formAvulsa) //copia
        this.formResultadoIntegradoDefault = _.cloneDeep(this.formResultadoIntegrado) //copia
        this.atualizar();
        this.listaVagas();
    },
    methods: {
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo;
            if (this.selecionaTudo) {
                this.comAdm.map(item => {
                    let id = item.curriculo_id;
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id)
                    }
                });
            } else {
                this.comAdm.map(item => {
                    let id = item.curriculo_id;
                    let index = this.selecionados.indexOf(id);
                    if (index >= 0) {
                        this.selecionados.splice(index, 1)
                    }
                });
            }
        },
        // AVULSA
        buscaCpf() {
            if (valida_cpf_vazio(this.$refs.cpf)) {
                if (this.formAvulsa.curriculo.cpf.length === 14) {
                    this.disabledInput = true;
                    this.exibiFormulario = false;
                    this.formAvulsa.preload = true;

                    axios.post(`${URL_ADMIN}/admissao/busca-cpf`, {
                        cpf: this.formAvulsa.curriculo.cpf
                    }).then(response => {
                        let data = response.data;
                        if (data.achou) {
                            Object.assign(this.formAvulsa, response.data);
                            this.exibiFormulario = true;
                            this.formAvulsa.preload = false;
                        }

                        if (!data.achou) {
                            let cpf = this.formAvulsa.curriculo.cpf;
                            this.formAvulsa = _.cloneDeep(this.formAvulsaDefault);
                            this.formAvulsa.curriculo.cpf = cpf;
                            this.exibiFormulario = true;
                            this.formAvulsa.preload = false;
                        }
                    })
                        .catch(error => {
                            this.formAvulsa.preload = false;
                            this.disabledInput = false;
                            this.exibiFormulario = false;
                        })
                }
            } else {
                this.disabledInput = false;
                this.exibiFormulario = false;
                this.formAvulsa.preload = false;
            }

        },

        selecionaMunicipioModal(obj) {
            this.formAvulsa.curriculo.municipio_id = obj.id;
            this.formAvulsa.curriculo.autocomplete_label_municipio_modal = obj.label;
            this.formAvulsa.curriculo.autocomplete_label_municipio_modal_anterior = obj.label;
        },

        resetaCampoMunicipioModal() {
            if (this.formAvulsa.curriculo.autocomplete_label_municipio_modal_anterior !== this.formAvulsa.curriculo.autocomplete_label_municipio_modal) {
                this.formAvulsa.curriculo.autocomplete_label_municipio_modal_anterior = '';
                this.formAvulsa.curriculo.autocomplete_label_municipio_modal = '';
                this.formAvulsa.curriculo.municipio_id = '';

                setTimeout(() => {
                    if (this.formAvulsa.curriculo.municipio_id === '') {
                        valida_campo_vazio($('#mun_' + this.hash), 1);
                        $('#janelaAdmissaoAvulsa #mun_' + this.hash).focus().trigger('blur');
                        mostraErro('Erro', 'O Campo Município não pode ficar vazio');
                    }
                }, 100);
            }
        },

        selecionaVagaModal(obj) {
            this.formAvulsa.feedback.vaga_id = obj.id;
            this.formAvulsa.feedback.autocomplete_label_vaga_modal = obj.label;
            this.formAvulsa.feedback.autocomplete_label_vaga_modal_anterior = obj.label;
        },

        resetaCampoVagaModal() {
            if (this.formAvulsa.feedback.autocomplete_label_vaga_modal_anterior !== this.formAvulsa.feedback.autocomplete_label_vaga_modal) {
                this.formAvulsa.feedback.autocomplete_label_vaga_modal_anterior = '';
                this.formAvulsa.feedback.autocomplete_label_vaga_modal = '';
                this.formAvulsa.feedback.vaga_id = '';
                setTimeout(() => {
                    if (this.formAvulsa.feedback.vaga_id === '') {
                        mostraErro('Erro', 'O Campo Vaga não pode ficar vazio');
                    }
                }, 100);
            }
        },

        selecionaClienteModal(obj) {
            this.formAvulsa.feedback.cliente_id = obj.id;
            this.formAvulsa.feedback.autocomplete_label_cliente_modal = obj.label;
            this.formAvulsa.feedback.autocomplete_label_cliente_modal_anterior = obj.label;
        },
        resetaCampoClienteModal() {
            if (this.formAvulsa.feedback.autocomplete_label_cliente_modal_anterior !== this.formAvulsa.feedback.autocomplete_label_cliente_modal) {
                this.formAvulsa.feedback.autocomplete_label_cliente_modal_anterior = '';
                this.formAvulsa.feedback.autocomplete_label_cliente_modal = '';
                this.formAvulsa.feedback.cliente_id = '';
                setTimeout(() => {
                    if (this.formAvulsa.feedback.cliente_id === '') {
                        mostraErro('', 'O Campo Cliente não pode ficar vazio');
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


        formVisualizar(id) {
            this.form.id = id;
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;
            this.visualizar = true;

            this.preload = true;
            Object.assign(this.form, this.formDefault);

            formReset();
            axios.get(`${URL_ADMIN}/preadmissao/${id}`)
                .then(response => {
                    let data = response.data.curriculo;
                    Object.assign(this.form, data);
                    this.form.id = response.data.id;
                    this.tituloJanela = `Documentos de Pré-admissão - ${data.nome}`;
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
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

        janelaConfirmar(id) {
            this.form.id = id;
            this.apagado = false;

            this.preload = false;
        },
        carregou(dados) {
            this.lista = dados.items;
            this.cliente_id = dados.usuario_cliente_id;
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
