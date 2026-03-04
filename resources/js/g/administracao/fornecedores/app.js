import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import telefone from '../../../components/Telefones'
import endereco from '../../../components/Endereco'
import datepicker from '../../../components/DatePicker'
import upload from '../../../components/Upload'

// Constantes
const API_ENDPOINTS = {
    FORNECEDOR: `${URL_ADMIN}/administracao/fornecedor`,
    FORNECEDOR_EDITAR: (id) => `${URL_ADMIN}/administracao/fornecedor/${id}/editar`,
    FORNECEDOR_BUSCAR_CPF: `${URL_ADMIN}/administracao/fornecedor/buscar-cpf`,
    CNPJ_BUSCA: `${URL_PUBLICO}/cnpjbusca`
}

const TELEFONE_PADRAO = {
    tipo: 'whatsapp',
    pais: 55,
    numero: '',
    ramal: '',
    detalhe: ''
}

const app = createApp({
    components: {
        telefone,
        endereco,
        datepicker,
        upload
    },
    data() {
        return {
            tituloJanela: 'Cadastrando Fornecedor',
            preloadAjax: false,
            editando: false,
            apagado: false,

            preloadCnpj: false,

            pages: 10,

            form: {
                tipo: 'Fornecedor',
                cnpj: '',
                cpf: '',
                nome: '',
                tipo_pessoa: 'pessoa_jurídica',
                razao_social: '',
                nome_fantasia: '',
                cep: '',
                logradouro: '',
                numero: '',
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

                telefones: [
                    {
                        tipo: 'whatsapp',
                        pais: 55,
                        numero: '',
                        ramal: '',
                        detalhe: '',
                        principal: true
                    }
                ],
                telefonesDelete: []
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
                    campoBusca: '',
                    campoTipo: '',
                    campoStatus: ''
                }
            }
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.atualizar()
    },
    methods: {
        addLIServicoFornecedor() {
            const obj = {}
            obj.nova = true
            obj.tipo_servico_fornecedor_id = ''
            obj.vencimento = ''
            obj.data_inicio = moment().format('L')
            obj.data_encerramento = moment().add(12, 'months').format('L')
            obj.escopo = ''
            obj.valor = ''
            obj.tipo_faturamento = 'Único'
            obj.status = 'Iniciado'
            obj.feedback = ''
            obj.ativo = true

            obj.anexos = []
            obj.anexosDel = []
            this.form.servicos.unshift(obj)
        },
        removerLIServicoFornecedor(index) {
            if (this.editando) {
                this.form.servicosDelete.push(this.form.servicos[index].id)
            }
            this.form.servicos.splice(index, 1)
        },

        formNovo() {
            this.cadastrado = false
            this.atualizado = false
            this.editando = false

            this.tituloJanela = 'Cadastrando Fornecedor'

            formReset()
            setupCampo()

            this.form = _.cloneDeep(this.formDefault) //copia
            this.leitura = false
        },
        async cadastrar() {
            try {
                if (!this.validarFormulario()) return

                this.preloadAjax = true
                this.marcaTelefonePrincial()
                this.form.numero = this.form.end_numero
                const response = await axios.post(API_ENDPOINTS.FORNECEDOR, this.form)

                if (response.status === 201) {
                    this.cadastrado = true
                    this.atualizar()
                }
            } catch (error) {
                this.tratarErro(error, 'Erro ao cadastrar fornecedor')
            } finally {
                this.preloadAjax = false
            }
        },
        validarFormulario() {
            formReset()
            $('#janelaCadastrar :input:enabled').trigger('blur')

            this.validarAbas()

            if ($('#janelaCadastrar :input:enabled.is-invalid').length) {
                mostraErro('', 'Verificar os erros')
                return false
            }

            if (this.form.telefones.length === 0) {
                mostraErro('', 'Por favor insira um Telefone')
                return false
            }

            return true
        },
        validarAbas() {
            const dadosCadastrais = $('#nav-dados-cadastrais :input:enabled.is-invalid').length > 0
            const servicos = $('#nav-servicos :input:enabled.is-invalid').length > 0

            $('#nav-dados-cadastrais-tab').toggleClass('bg-danger text-white', dadosCadastrais)
            $('#nav-servicos-tab').toggleClass('bg-danger text-white', servicos)
        },
        async formAlterar(id) {
            try {
                this.inicializarFormulario()
                this.preloadAjax = true

                const response = await axios.get(API_ENDPOINTS.FORNECEDOR_EDITAR(id))
                Object.assign(this.form, response.data)
                this.form.end_numero = response.data.numero

                this.editando = true
                setupCampo()
            } catch (error) {
                this.tratarErro(error, 'Erro ao carregar dados do fornecedor')
            } finally {
                this.preloadAjax = false
            }
        },
        inicializarFormulario() {
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.tituloJanela = 'Alterando Fornecedor'
            formReset()
            this.form = _.cloneDeep(this.formDefault)
            this.leitura = true
        },

        marcaTelefonePrincial() {
            if (this.form.telefones.length > 0 && !this.form.telefones.some((t) => t.principal)) {
                this.form.telefones[0].principal = true
            }
        },

        async alterar() {
            try {
                if (!this.validarFormulario()) return

                this.form._method = 'PUT'
                this.preloadAjax = true

                this.marcaTelefonePrincial()
                this.form.numero = this.form.end_numero

                await axios.put(`${API_ENDPOINTS.FORNECEDOR}/${this.form.id}`, this.form)

                this.atualizado = true
                this.atualizar()
            } catch (error) {
                this.tratarErro(error, 'Erro ao alterar fornecedor')
            } finally {
                this.preloadAjax = false
            }
        },
        async apagar() {
            try {
                this.erros = []
                this.form._method = 'DELETE'
                this.preloadAjax = true

                await axios.delete(`${API_ENDPOINTS.FORNECEDOR}/${this.form.id}`, this.form)

                this.apagado = true
                this.atualizar()
            } catch (error) {
                this.tratarErro(error, 'Erro ao apagar fornecedor')
            } finally {
                this.preloadAjax = false
            }
        },
        tratarErro(error, mensagemPadrao) {
            console.error(error)
            mostraErro('', error.response?.data?.message || mensagemPadrao)
        },
        async verificaCnpj() {
            if (this.editando || this.form.cnpj.length !== 18) return

            try {
                const numsStr = this.form.cnpj.replace(/[^0-9]/g, '')
                const cnpj = parseInt(numsStr)

                this.preloadCnpj = true
                const response = await axios.post(API_ENDPOINTS.CNPJ_BUSCA, { cnpj })

                if (response.data.status === 'OK') {
                    this.preencherDadosCnpj(response.data)
                } else {
                    this.limparDadosCnpj()
                }
            } catch (error) {
                this.tratarErro(error, 'Erro ao consultar CNPJ')
            } finally {
                this.preloadCnpj = false
            }
        },
        preencherDadosCnpj(data) {
            this.form.razao_social = data.nome
            this.form.nome_fantasia = data.fantasia
            this.form.cep = replaceAll(data.cep, '.', '')
            this.form.logradouro = data.logradouro
            this.form.end_numero = data.numero
            this.form.complemento = data.complemento
            this.form.bairro = data.bairro
            this.form.municipio = data.municipio
            this.form.uf = data.uf
        },
        limparDadosCnpj() {
            const campos = ['razao_social', 'nome_fantasia', 'cep', 'logradouro', 'end_numero', 'complemento', 'bairro', 'municipio', 'uf']
            campos.forEach((campo) => (this.form[campo] = ''))
        },
        janelaConfirmar(id) {
            this.form.id = id
            this.apagado = false

            this.preloadAjax = false
        },
        carregou(dados) {
            this.lista = dados.itens
            this.listaServicos = dados.servicos
            this.listaAreas = dados.areas
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },

        atualizar() {
            this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        },

        verificaCpf() {
            if (!this.editando) {
                axios.get(`${URL_ADMIN}/administracao/fornecedor/buscar-cpf?cpf=${this.form.cpf}`).then((response) => {})
            }
        }
    }
})

registerGlobals(app)
app.mount('#app')
