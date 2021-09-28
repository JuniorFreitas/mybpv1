import Dados from '../../components/entrevistas/DadosPessoaisTexto'
import Formulario from '../../components/FormularioDefault'

const app = new Vue({
    el: '#app',
    components: {
        Formulario,
        Dados
    },
    data: {
        tituloJanela: 'Controle de Exames',
        preload: false,
        editando: false,
        apagado: false,
        cadastrado: false,
        cadastrando: false,
        atualizado: false,
        visualizar: false,

        hash: `mastertag_${parseInt(Math.random() * 999999)}`,

        resposta_id: 0,
        formulario_id: 2,

        dados: {
            nome: '',
            cargo: ''
        },

        concordo: false,
        abasesmt: {
            tituloJanela: 'Resultado',
            preload: false,
            form: {
                exame_funcionario_id: '',
                exame_realizado: '',
                data_realizacao: '',
                resultado: {
                    result: '',
                    pendencias: '',
                    pendencias_quais: '',
                    aprovado: '',
                    trabalho_altura: '',
                    espacao_confinado: '',
                    observacoes: ''
                }
            },
            formDefault: null
        },

        form: {
            formulario: null,
            respostas: {},
            feedback_id: 0,
            empresa_exame_id: '',
            empresa_id: 0
        },

        formDefault: null,

        colunasTabela: {
            cliente: false
        },

        URL_ADMIN,
        AUTENTICADO,
        estados: ESTADOS,
        todos_municipios: `autocomplete/todos-municipios`,

        urlPaginacao: `${URL_ADMIN}/controle-exames/atualizar`,
        exibicao: EXIBICAO,

        lista: [],
        listaEmpresasExames: [],
        historico: [],

        controle: {
            carregando: true,
            dados: {
                filtroPeriodo: false,
                periodo: '',
                campoBusca: '',
                campoCPF: '',
                campoUf: '',
                pages: EXIBICAO[0]
            }
        }
    },
    async mounted() {
        await this.atualizar()
        await this.initForm()
        this.usuarioAutenticado()
        this.formDefault = _.cloneDeep(this.form)
        this.abasesmt.formDefault = _.cloneDeep(this.abasesmt.form)
    },
    computed: {
        comResultado() {
            return this.lista.filter(item => {
                return item.resultado_integrado
            })
        },
        tudoMarcado() {
            let totalItens = this.comResultado.length
            let totalEncontrado = 0

            if (totalItens === 0) {
                return false
            }

            this.comResultado.forEach(item => {
                let id = item.curriculo_id
                if (this.selecionados.indexOf(id) >= 0) {
                    totalEncontrado++
                } else {
                    return false
                }
            })
            let resultado = totalItens === totalEncontrado
            this.selecionaTudo = resultado
            return resultado
        }
    },
    methods: {
        async carregaFormulario() {
            await axios.get(`${URL_ADMIN}/formulario/${this.formulario_id}`)
                .then(response => {
                    this.form.formulario = response.data
                }).catch(error => {
                })
        },

        async initForm() {
            await this.carregaFormulario()
            this.form.respostas = _.cloneDeep(this.form.respostas)
        },

        async formEncaminhar(obj) {
            this.dados.nome = obj.curriculo.nome
            this.dados.cargo = obj.vaga_selecionada.nome

            this.form = _.cloneDeep(this.formDefault)
            this.form.feedback_id = obj.id
            this.tituloJanela = `#${obj.id} - ${obj.curriculo.nome}`
            this.preload = true

            this.cadastrado = false
            this.atualizado = false
            this.cadastrando = false
            this.visualizar = false
            this.editando = false
            await this.initForm()
            formReset()

            await this.carregaFormularioResposta()
        },

        async carregaFormularioResposta() {
            await axios.get(`${URL_ADMIN}/controle-exames/carregaResposta`, {
                params: {
                    feedback_id: this.form.feedback_id,
                    formulario: this.formulario_id
                }
            }).then(response => {
                let data = response.data
                Object.assign(this.form, data.result)
                // if (data.tipo === 'cadastrar') {
                this.cadastrando = true
                this.editando = false
                this.historico = data.historico
                // } else {
                //     this.editando = true
                //     this.cadastrando = false
                // }
                this.preload = false
            }).catch(error => {
                this.preload = true
            })
        },

        async salvarUpdate() {
            $('#formdinamico :input:visible').trigger('blur')
            $('#janelaParecerEntrevista :input:visible').trigger('blur')
            if ($('#janelaParecerEntrevista :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true
            const URL = `${URL_ADMIN}/controle-exames/salvaUpdate`

            if (this.cadastrando) {
                await axios.post(URL, {
                    formulario_id: this.formulario_id,
                    feedback_id: this.form.feedback_id,
                    empresa_exame_id: this.form.empresa_exame_id,
                    respostas: this.form.respostas,
                    tipo: 'store'
                }).then(res => {
                    let data = res.data
                    mostraSucesso('', 'Exame cadastrado com sucesso!')
                    $('#janelaParecerEntrevista').modal('hide')
                    this.$refs.componente.buscar()
                    this.preload = false
                }).catch(error => this.preload = false)
            } else {
                await axios.put(URL, {
                    id: this.form.id,
                    empresa_exame_id: this.form.empresa_exame_id,
                    respostas: this.form.respostas,
                    tipo: 'update'
                }).then(res => {
                    let data = res.data
                    mostraSucesso('', 'Exame atualizado com sucesso!')
                    $('#janelaParecerEntrevista').modal('hide')
                    this.$refs.componente.buscar()
                    this.preload = false
                }).catch(error => this.preload = false)
            }
        },

        async formResultado(id) {
            this.abasesmt.form = _.cloneDeep(this.abasesmt.formDefault)
            this.abasesmt.form.exame_funcionario_id = id;
            this.abasesmt.tituloJanela = `Resultado do exame de ${form.curriculo.nome}`
            // this.preload = true

        },

        async salvarResultado() {
            $('#validaSesmt :input:visible').trigger('blur')
            if ($('#validaSesmt :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.abasesmt.preload = true

            const URL = `${URL_ADMIN}/controle-exames/salvaResultado`

            await axios.post(URL, this.abasesmt.form).then(res => {
                let data = res.data
                mostraSucesso('', 'Exame cadastrado com sucesso!')
                $('#validaSesmt').modal('hide')
                this.$refs.componente.buscar()
                this.abasesmt.preload = false
            }).catch(error => this.abasesmt.preload = false)

            /*if (this.cadastrando) {
                await axios.post(URL, {
                    formulario_id: this.formulario_id,
                    feedback_id: this.form.feedback_id,
                    empresa_exame_id: this.form.empresa_exame_id,
                    respostas: this.form.respostas,
                    tipo: 'store'
                }).then(res => {
                    let data = res.data
                    mostraSucesso('', 'Exame cadastrado com sucesso!')
                    $('#janelaParecerEntrevista').modal('hide')
                    this.$refs.componente.buscar()
                    this.preload = false
                }).catch(error => this.preload = false)
            } else {
                await axios.put(URL, {
                    id: this.form.id,
                    empresa_exame_id: this.form.empresa_exame_id,
                    respostas: this.form.respostas,
                    tipo: 'update'
                }).then(res => {
                    let data = res.data
                    mostraSucesso('', 'Exame atualizado com sucesso!')
                    $('#janelaParecerEntrevista').modal('hide')
                    this.$refs.componente.buscar()
                    this.preload = false
                }).catch(error => this.preload = false)
            }*/
        },

        usuarioAutenticado() {
            this.form.empresa_id = this.AUTENTICADO.empresa_id
            this.colunasTabela.cliente = this.AUTENTICADO.cliente_id === 0
            this.controle.dados.campoCliente = this.AUTENTICADO.cliente_id !== 0 ? this.AUTENTICADO.cliente_id : this.controle.dados.campoCliente
        },

        carregou(dados) {
            this.lista = dados.itens
            this.listaEmpresasExames = dados.emp_exames
            this.selecionaTudo = this.tudoMarcado
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.$refs.componente.atual = 1
            this.$refs.componente.buscar()
        }
    }
})
