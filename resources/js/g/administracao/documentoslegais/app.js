import telefone from '../../../components/Telefones';
import endereco from '../../../components/Endereco';
import datepicker from '../../../components/DatePicker';
import upload from '../../../components/Upload';

const app = new Vue({
    el: '#app',
    components: {
        telefone,
        endereco,
        datepicker,
        upload
    },
    data: {
        tituloJanela: 'Cadastrando Documento Legais',
        preloadAjax: false,
        editando: false,
        apagado: false,

        pages: 10,

        listaDeHabilidades: [],
        todosMenu: [],
        todasHabilidades: true,
        menuHabilidades: true,

        form: {
            dados_cadastrais: {
                cnpj: '',
                cpf: '',
                nome: '',
                tipo: 'Pessoa Jurídica',
                razao_social: '',
                nome_fantasia: '',
                area_id: '',
                ramo: '',
                cep: '',
                logradouro: '',
                numero: '',
                complemento: '',
                bairro: '',
                municipio: '',
                uf: '',
                responsavel: '',
                email: '',

                tel_principal: '',

                anexosDel: [],
                anexosProspectDel: [],

                logo: [],
                logoDel: [],

                telefones: [{
                    tipo: 'comercial',
                    pais: 55,
                    numero: '',
                    ramal: '',
                    detalhe: '',
                }],
                telefonesDelete: [],

            },
            tipo_documento: '',
            ativo: '',
            servicos_cliente: [],
            servicos_clienteDelete: [],

            servicos_prospect: [],
            servicos_prospectDelete: [],
        },

        urlAnexoUpload: `${URL_ADMIN}/administracao/documentoslegais/uploadAnexos`,
        anexoUploadAndamento: false,

        urlLogoUpload: `${URL_ADMIN}/administracao/documentoslegais/uploadLogo`,
        logoUploadAndamento: false,

        urlMascoteUpload: `${URL_ADMIN}/administracao/documentoslegais/uploadMascote`,
        mascoteUploadAndamento: false,

        formDefault: null,
        campoNome: null,

        cadastrado: false,
        atualizado: false,
        leitura: false,

        lista: [],
        listaServicos: [],
        listaAreas: [],

        controle: {
            carregando: false,
            dados: {
                campoBusca: "",
                campoTipo: "",
                campoStatus: "",
            },
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.atualizar();
    },
    methods: {
        selecionarTodas() {
            this.todasHabilidades = !this.todasHabilidades;
            var valor = this.todasHabilidades;
            _.forEach(this.listaDeHabilidades, function (habilidade) {
                habilidade.acesso = valor;
            });
        },
        selecionarPorModulo(menu) {
            this.menuHabilidades = !this.menuHabilidades;
            var valor = this.menuHabilidades;
            _.forEach(this.listaDeHabilidades, function (habilidade) {
                if (habilidade.menu === menu) {
                    habilidade.acesso = valor;
                }
            });
        },
        addLIServicoCliente() {
            const obj = {};
            obj.nova = true;
            obj.servico_id = '';
            obj.data_inicio = moment().format('L');
            obj.data_encerramento = moment().add(6, 'months').format('L');
            obj.escopo = '';
            obj.valor = '0.00';
            obj.tipo_faturamento = 'Único';
            obj.status = 'Iniciado';
            obj.feedback = '';
            obj.tipo_contrato = 'FIXO';
            obj.ativo = true;

            obj.anexos = [];
            obj.anexosDel = [];
            this.form.servicos_cliente.unshift(obj);
        },
        removerLIServicoCliente(index) {
            if (this.editando) {
                this.form.servicos_clienteDelete.push(this.form.servicos_cliente[index].id);
            }
            this.form.servicos_cliente.splice(index, 1);
        },
        addLIServicoProspect() {
            const obj = {};
            obj.nova = true;
            obj.servico_id = '';
            obj.data_envio_proposta = moment().format('L');
            obj.escopo = '';
            obj.status = 'Iniciado';
            obj.feedback = '';
            obj.anexos = [];
            obj.anexosDel = [];

            this.form.servicos_prospect.unshift(obj);
        },
        removerLIServicoProspect(index) {
            if (this.editando) {
                this.form.servicos_prospectDelete.push(this.form.servicos_prospect[index].id);
            }
            this.form.servicos_prospect.splice(index, 1);
        },
        formNovo() {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;

            this.tituloJanela = "Cadastrando Documento Legais";

            formReset();
            setupCampo();

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = false;

        },
        cadastrar() {
            formReset();

            $('#janelaCadastrar :input:enabled').trigger('blur');
            // Validações de abas
            $('#nav-dados-cadastrais :input:enabled.is-invalid').length > 0 ? $('#nav-dados-cadastrais-tab').addClass('bg-danger text-white') : $('#nav-dados-cadastrais-tab').removeClass('bg-danger text-white');
            $('#nav-servicos :input:enabled.is-invalid').length > 0 ? $('#nav-servicos-tab').addClass('bg-danger text-white') : $('#nav-servicos-tab').removeClass('bg-danger text-white');

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }

            this.form.listaDeHabilidades = this.listaDeHabilidades;
            this.preloadAjax = true;
            axios.post(`${URL_ADMIN}/administracao/documentoslegais`, this.form)
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
            this.tituloJanela = "Alterando Documento Legais";
            this.preloadAjax = true;
            formReset();

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = true;

            axios.get(`${URL_ADMIN}/administracao/documentoslegais/${id}/editar`)
                .then(response => {
                    Object.assign(this.form, response.data.cliente);
                    this.editando = true;
                    this.preloadAjax = false;
                    if (!response.data.cliente.cliente_config) {
                        this.form.cliente_config = {
                            'verifica_mes_vencimento': '',
                            'envia_whatsapp': '',
                            'vencimento_aso': '',
                        }
                    }

                    this.listaDeHabilidades = response.data.listaDeHabilidades;
                    this.todosMenu = response.data.todosMenu;

                    var habilidades_papel = response.data.cliente.papel.habilidades;
                    _.forEach(this.listaDeHabilidades, function (habilidade) {

                        var achou = _.find(habilidades_papel, {'id': habilidade.id});
                        if (achou) {
                            habilidade.acesso = true;
                        }
                    });

                    setupCampo();
                }).catch(
                error => (this.preloadAjax = false)
            );

        },

        alterar() {
            formReset();
            $('#janelaCadastrar :input:enabled').trigger('blur');
            // Validações de abas
            $('#nav-dados-cadastrais :input:enabled.is-invalid').length > 0 ? $('#nav-dados-cadastrais-tab').addClass('bg-danger text-white') : $('#nav-dados-cadastrais-tab').removeClass('bg-danger text-white');
            $('#nav-servicos :input:enabled.is-invalid').length > 0 ? $('#nav-servicos-tab').addClass('bg-danger text-white') : $('#nav-servicos-tab').removeClass('bg-danger text-white');

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros');
                return false;
            }

            if (this.form.telefones.length === 0) {
                mostraErro('', 'Por favor insira um Telefone');
                return false;
            }

            this.form._method = 'PUT';
            this.form.listaDeHabilidades = this.listaDeHabilidades;
            this.preloadAjax = true;

            axios.put(`${URL_ADMIN}/administracao/documentoslegais/${this.form.id}`, this.form).then(response => {
                this.preloadAjax = false;
                this.atualizado = true;
                this.atualizar();
            }).catch(error => (this.preloadAjax = false));

        },
        apagar() {
            this.erros = [];
            this.form._method = 'DELETE';
            this.preloadAjax = true;

            axios.delete(`${URL_ADMIN}/administracao/documentoslegais/${this.form.id}`, this.form)
                .then(response => {
                    this.preloadAjax = false;
                    this.apagado = true;
                    this.atualizar();
                }).catch(error => (this.preloadAjax = false));

        },

        janelaConfirmar(id) {
            this.form.id = id;
            this.apagado = false;

            this.preloadAjax = false;
        },
        carregou(dados) {
            this.lista = dados.itens;
            this.listaServicos = dados.servicos;
            this.listaAreas = dados.areas;
            this.listaDeHabilidades = dados.listaDeHabilidades;
            this.controle.carregando = false;

        },
        carregando() {
            this.controle.carregando = true;
        },

        atualizar() {
            this.$refs.componente.atual = 1;
            this.$refs.componente.buscar();
        },

        verificaCpf() {
            if (!this.editando) {
                axios.get(`${URL_ADMIN}/administracao/documentoslegais/buscar-cpf?cpf=${this.form.cpf}`)
                    .then(response => {
                    });
            }
        },
        verificaCnpj() {
            if (!this.editando) {
                axios.get(`${URL_ADMIN}/administracao/documentoslegais/buscar-cnpj?cnpj=${this.form.cnpj}`)
                    .then(response => {
                    });
            }
        }
    }
});
