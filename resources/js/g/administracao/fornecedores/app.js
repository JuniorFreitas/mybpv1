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
        tituloJanela: 'Cadastrando Fornecedor',
        preloadAjax: false,
        editando: false,
        apagado: false,

        preloadCnpj: false,

        pages: 10,

        form: {
            tipo: '',
            cnpj: '',
            cpf: '',
            nome: '',
            tipo_pessoa: 'pessoa_jurídica',
            razao_social: '',
            nome_fantasia: '',
            cep: '',
            logradouro: '',
            end_numero: '',
            complemento: '',
            bairro: '',
            municipio: '',
            uf: '',
            contato: '',
            email: '',
            ativo: true,

            servicos: [],
            servicosDelete: [],

            anexos: [],
            anexosDel: [],

            telefones: [{
                tipo: 'whatsapp',
                pais: 55,
                numero: '',
                ramal: '',
                detalhe: '',
            }],
            telefonesDelete: [],
        },

        urlAnexoUpload: `${URL_ADMIN}/administracao/fornecedor/uploadAnexos`,
        anexoUploadAndamento: false,

        urlAnexoServicoUpload: `${URL_ADMIN}/administracao/fornecedor/uploadAnexos`,
        anexoServicoUploadAndamento: false,

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

        addLIServicoFornecedor() {
            const obj = {};
            obj.nova = true;
            obj.tipo_servico_fornecedor_id = '';
            obj.vencimento = '';
            obj.data_inicio = moment().format('L');
            obj.data_encerramento = moment().add(12, 'months').format('L');
            obj.escopo = '';
            obj.valor = '';
            obj.tipo_faturamento = 'Único';
            obj.status = 'Iniciado';
            obj.feedback = '';
            obj.ativo = true;

            obj.anexos = [];
            obj.anexosDel = [];
            this.form.servicos.unshift(obj);
        },
        removerLIServicoFornecedor(index) {
            if (this.editando) {
                this.form.servicosDelete.push(this.form.servicos[index].id);
            }
            this.form.servicos.splice(index, 1);
        },

        formNovo() {
            this.cadastrado = false;
            this.atualizado = false;
            this.editando = false;

            this.tituloJanela = "Cadastrando Fornecedor";

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

            if (this.form.telefones.length === 0) {
                mostraErro('', 'Por favor insira um Telefone');
                return false;
            }

            this.preloadAjax = true;
            axios.post(`${URL_ADMIN}/administracao/fornecedor`, this.form)
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
            this.tituloJanela = "Alterando Fornecedor";
            this.preloadAjax = true;
            formReset();

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = true;

            axios.get(`${URL_ADMIN}/administracao/fornecedor/${id}/editar`)
                .then(response => {
                    Object.assign(this.form, response.data);
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
            this.preloadAjax = true;

            axios.put(`${URL_ADMIN}/administracao/fornecedor/${this.form.id}`, this.form).then(response => {
                this.preloadAjax = false;
                this.atualizado = true;
                this.atualizar();
            }).catch(error => (this.preloadAjax = false));

        },
        apagar() {
            this.erros = [];
            this.form._method = 'DELETE';
            this.preloadAjax = true;

            axios.delete(`${URL_ADMIN}/administracao/fornecedor/${this.form.id}`, this.form)
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
                axios.get(`${URL_ADMIN}/administracao/fornecedor/buscar-cpf?cpf=${this.form.cpf}`)
                    .then(response => {
                    });
            }
        },
        verificaCnpj() {
            if (!this.editando && this.form.cnpj.length === 18) {
                let numsStr = this.form.cnpj.replace(/[^0-9]/g, '');
                let cnpj = parseInt(numsStr);

                this.preloadCnpj = true;
                axios.post(`${URL_PUBLICO}/cnpjbusca`, {cnpj: cnpj})
                    .then(response => {
                        let data = response.data;
                        if (data.status === 'OK') {
                            this.form.razao_social = data.nome;
                            this.form.nome_fantasia = data.fantasia;
                            this.form.cep = replaceAll(data.cep, '.', '');
                            this.form.logradouro = data.logradouro;
                            this.form.end_numero = data.numero;
                            this.form.complemento = data.complemento;
                            this.form.bairro = data.bairro;
                            this.form.municipio = data.municipio;
                            this.form.uf = data.uf;
                        } else {
                            this.form.razao_social = '';
                            this.form.nome_fantasia = '';
                            this.form.cep = '';
                            this.form.logradouro = '';
                            this.form.end_numero = '';
                            this.form.complemento = '';
                            this.form.bairro = '';
                            this.form.municipio = '';
                            this.form.uf = '';
                        }
                        this.preloadCnpj = false;
                    });

            }
        }
    }
});
