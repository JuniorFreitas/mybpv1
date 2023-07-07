import upload from '../../../components/Upload';
import validacoes from "../../../mixins/Validacoes";
import configuracoes from "../../../mixins/Configuracoes";

const app = new Vue({
    el: '#app',
    mixins: [validacoes,configuracoes],
    components: {
        upload,
    },
    data: {
        tituloJanela: 'Pré-admissão',
        tituloJanelaFinalizar: '',
        preload: false,
        preloadFinalizar: false,
        editando: false,
        apagado: false,
        cadastrado: false,
        cadastrando: false,
        atualizado: false,
        visualizar: false,
        disabled: true,
        disabledInput: false,
        btnBuscar: false,
        email_padrao: '',

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

        form: {
            id: '',
            nome: '',
            cpf: '',
            docs_curriculo_pre_adm: [], //DOCUMENTOS DO CURRICULO
        },
        formDefault: null,

        formEmail: {
            id: '',
            email: '',
            curriculo_id: '',
            observacao: '',
            temwhatsapp: false,
            envia_whatsapp: false,
            numero_telefone: '',
        },
        formEmailDefault: null,

        formFinalizar: {
            feedback_id: '',
            empresa_exame_id: '',
            encaminhado_exame_data: '',
            pcmso_id: '',
            envia_email: true,
            envia_whatsapp: true,
        },
        formFinalizarDefault: {
            feedback_id: '',
            empresa_exame_id: '',
            encaminhado_exame_data: '',
            pcmso_id: '',
            envia_email: true,
            envia_whatsapp: true,
        },

        lista: [],
        listaPcmsos: [],
        listaEmpresasExames: [],
        dadosFinalizar: [],
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
                campoUf: '',
                status: 'em_processo',
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
        },
        dataHoje() {
            return new Date(Date.now()).toLocaleString().split(',')[0];
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.formEmailDefault = _.cloneDeep(this.formEmail) //copia
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
                    let data = response.data;
                    Object.assign(this.form, data);
                    this.form.id = response.data.id;
                    this.formFinalizar.t
                    this.tituloJanela = `Documentos de Pré-admissão - ${data.curriculo.nome}`;
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        abrirFormFinalizar(id) {
            this.aprovado = false;
            this.aprovando = false;
            this.preloadFinalizar = true;
            Object.assign(this.formFinalizar, this.formFinalizarDefault);
            this.formFinalizar = _.cloneDeep(this.formFinalizarDefault); //copia
            formReset();

            axios.get(`${URL_ADMIN}/preadmissao/finalizar/${id}`)
                .then(({data}) => {
                    this.dadosFinalizar = data.dados;
                    this.listaPcmsos = data.pcmsos;
                    this.listaEmpresasExames = data.empresas_exames;
                    this.tituloJanelaFinalizar = `Finalizar Pré-admissão - ${this.dadosFinalizar.curriculo.nome}`;
                    this.preloadFinalizar = false;
                })
                .catch(error => {
                    this.preloadFinalizar = false;
                })
        },

        formEnviarEmail(id) {
            this.formEmail.id = id;
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;

            this.preload = true;
            Object.assign(this.formEmail, this.formEmailDefault);

            formReset();
            axios.get(`${URL_ADMIN}/preadmissao/editar/${id}`)
                .then(response => {
                    let data = response.data;
                    this.formEmail.curriculo_id = data.curriculo.id;
                    this.formEmail.id = data.id;
                    this.formEmail.email = data.curriculo.email;
                    this.formEmail.temwhatsapp = data.tel_principal.tipo === 'whatsapp';
                    this.formEmail.envia_whatsapp = data.tel_principal.tipo === 'whatsapp';
                    this.formEmail.numero_telefone = data.tel_principal.sonumero;
                    this.tituloJanela = `Reenvio de E-mail - ${data.curriculo.nome}`;
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        enviarEmail() {
            this.formEmail.email = this.formEmail.email.toLowerCase().trim();

            this.validaBlur();
            $(`#janelaEnviarEmail :input:visible.is-invalid`).length;
            if ($(`#janelaEnviarEmail :input:visible.is-invalid`).length) {
                this.mostraErro("", "Preencha todos os campos obrigatórios");
                return false;
            }

            if(this.formEmail.email === this.email_padrao.toLowerCase().trim()){
                this.mostraErro('', `Preencha com um e-mail diferente de ${this.email_padrao}`)
                return false;
            }

            this.preload = true;

            formReset();
            axios.post(`${URL_ADMIN}/preadmissao/enviar-email`, this.formEmail)
                .then(response => {
                    if (response.status === 201) {
                        $('#janelaEnviarEmail').modal('hide');
                        this.mostraSucesso('Envio do e-mail concluído com sucesso!');
                    }
                    this.preload = false;
                })
                .catch(error => {
                    this.preload = false;
                })
        },

        finalizarEncaminhar(id) {
            this.validaBlur();

            $("#janelaFinalizar :input:visible").trigger("blur");
            if ($("#janelaFinalizar :input:visible.is-invalid").length) {
                mostraErro("", "Preencha todos os campos obrigatórios");
                return false;
            }

            this.preloadFinalizar = true;
            this.formFinalizar.feedback_id = id;
            formReset();
            axios.post(`${URL_ADMIN}/preadmissao/finalizar-encaminhar`, this.formFinalizar)
                .then(response => {
                    if (response.status === 201) {
                        this.formFinalizar = _.cloneDeep(this.formFinalizarDefault); //copia
                        $('#janelaFinalizar').modal('hide');
                        this.mostraSucesso('Finalizado e encaminhado com sucesso!');
                        this.atualizar();
                    }
                    this.preloadFinalizar = false;
                })
                .catch(error => {
                    this.preloadFinalizar = false;
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
            this.email_padrao = dados.email_padrao;
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
