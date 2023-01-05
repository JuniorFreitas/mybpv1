import upload from '../../../components/Upload';
import validacoes from "../../../mixins/Validacoes";

const app = new Vue({
    el: '#app',
    mixins: [validacoes],
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

        formEmail: {
            id: '',
            email: '',
            curriculo_id: '',
            observacao: ''
        },
        formEmailDefault: null,

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
