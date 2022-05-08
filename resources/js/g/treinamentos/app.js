import datepicker from '../../components/DatePicker'

const app = new Vue({
    el: '#app',
    components: {
        datepicker
    },
    data: {
        tituloJanela: 'Treinamentos',
        preload: false,
        editando: false,
        apagado: false,
        cadastrado: false,
        cadastrando: false,
        atualizado: false,
        visualizar: false,
        disabled: true,

        URL_ADMIN,

        hash: `mastertag_${parseInt((Math.random() * 999999))}`,

        cliente_id: '',

        todos_municipios: `autocomplete/todos-municipios`,

        selecionados: [],
        selecionaTudo: false,

        selecionadosMassa: [],
        selecionaTudoMassa: false,

        form: {
            //_method: "post",
            feedback_id: '',
            curriculo_id: '',
            tipo: '',
            gerou_id: '',
            data_envio: '',
            enviado_email: '',
            enviou_id: '',
            email_envio: '',
            email_aberto: '',
            data_email_aberto: '',
            listaVencimentos: [],
            nr_trinta_tres: true,
            nr_trinta_cinco: true,
            exame: {
                feedback_id: '',
                exame_realizado: '',
                data_realizado: '',
                tipo_exame: '',
                trabalho_altura: '',
                espaco_confinado: ''
            }
        },
        formDefault: null,

        formMassa: {
            tipo: '',
            gerou_id: '',
            data_envio: '',
            enviado_email: '',
            enviou_id: '',
            email_envio: '',
            email_aberto: '',
            data_email_aberto: '',
            listaVencimentos: [],
            nr_trinta_tres: true,
            nr_trinta_cinco: true,
            selecionadosMassa: '',
            exame: {
                feedback_id: '',
                exame_realizado: '',
                data_realizado: '',
                tipo_exame: '',
                trabalho_altura: '',
                espaco_confinado: ''
            }
        },
        formMassaDefault: null,
        vencimentos: [],


        formEnviar: {
            enviado: false,
            preload: false,
            titulo: 'Enviar Carteira e Etiqueta',
            nome: '',
            email: '',
            token: ''
        },
        formEnviarDefault: null,

        formEnviarAviso: {
            enviado: false,
            preload: false,
            email: ''
        },

        formEnviarAvisoDefault: null,

        lista: [],
        vagas: [],
        listaAreas: [],

        controle: {
            carregando: false,
            dados: {
                caminho_autocomplete: `autocomplete/todas-vagas-ativas`,
                autocomplete_label_anterior: '',
                autocomplete_label: '',
                autocomplete_label_cliente_anterior: '',
                autocomplete_label_cliente: '',
                pages: 20,
                cliente_custom: '',
                campoBusca: '',
                campoVaga: '',
                campoLido: '',
                campoFiltro: '',
                campoPcd: '',
                campoUf: '',
                campoArea: '',
                campoCargo: '',
                campo_treinados: '',
                campoNr_trinta_tres: '',
                campoNr_trinta_cinco: '',
                campoNr_ebtv: '',
                campoAdmitido: '',
                campoCracha: '',
                campoFoto: '',
                campo_dataInicio: '',
                campo_dataFim: '',
                campoVencimento: '',
                vencimento: '',
            }
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.formEnviarDefault = _.cloneDeep(this.formEnviar) //copia
        this.formEnviarAvisoDefault = _.cloneDeep(this.formEnviarAviso) //copia
        this.cliente_id = $('#cliente_id').val()
        if (this.cliente_id) { //diferente de BPSE
            this.controle.dados.campoCliente = parseInt(this.cliente_id)
            this.controle.dados.cliente_custom = parseInt(this.cliente_id)
        }
        this.listaVagas()
        this.listaAreasGeral()
        this.atualizar()
    },
    computed: {
        emTreinamentos() {
            return this.lista.filter(item => {
                return item.treinamento
            })
        },
        tudoMarcado() {
            let totalTreinamento = this.emTreinamentos.length
            let totalEncontrado = 0

            if (totalTreinamento === 0) {
                return false
            }

            this.emTreinamentos.forEach(item => {
                let id = item.id
                if (this.selecionados.indexOf(id) >= 0) {
                    totalEncontrado++
                    //faz nada
                } else {
                    return false
                }
            })
            let resultado = totalTreinamento === totalEncontrado
            this.selecionaTudo = resultado
            return resultado
        },
        emTreinamentosMassa() {
            return this.lista.filter(item => {
                return item.treinamento
            })
        },
        tudoMarcadoMassa() {
            let totalTreinamento = this.emTreinamentosMassa.length
            let totalEncontrado = 0

            if (totalTreinamento === 0) {
                return false
            }

            this.emTreinamentosMassa.forEach(item => {
                let id = item.id
                if (this.selecionadosMassa.indexOf(id) >= 0) {
                    totalEncontrado++
                    //faz nada
                } else {
                    return false
                }
            })
            let resultado = totalTreinamento === totalEncontrado
            this.selecionaTudoMassa = resultado
            return resultado
        }
    },
    methods: {
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo
            if (this.selecionaTudo) {
                this.emTreinamentos.map(item => {
                    let id = item.id
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id)
                    }
                })
            } else {
                this.emTreinamentos.map(item => {
                    let id = item.id
                    let index = this.selecionados.indexOf(id)
                    if (index >= 0) {
                        this.selecionados.splice(index, 1)
                    }
                })
            }
        },

        selecionaTodosMassa() {
            this.selecionaTudoMassa = !this.selecionaTudoMassa
            if (this.selecionaTudoMassa) {
                this.lista.map(item => {
                    let id = item.id
                    if (this.selecionadosMassa.indexOf(id) === -1) {
                        this.selecionadosMassa.push(id)
                    }
                })
            } else {
                this.lista.map(item => {
                    let id = item.id
                    let index = this.selecionadosMassa.indexOf(id)
                    if (index >= 0) {
                        this.selecionadosMassa.splice(index, 1)
                    }
                })
            }
        },

        gerarCarteiras() {
            axios.get(`${URL_ADMIN}/treinamento/carteiras`, {selecionados: this.selecionados})
                .then(response => {
                    let data = response.data

                })
                .catch(error => {

                })
        },

        formCadastra() {
            this.form = _.cloneDeep(this.formDefault) //copia
            formReset()
            setupCampo()
        },

        formAlterar(curriculo_id) {
            this.preload = true

            this.atualizado = false
            this.cadastrando = false
            this.visualizar = false
            this.cadastrado = false
            this.form = _.cloneDeep(this.formDefault) //copia
            this.form.curriculo_id = curriculo_id;

            axios.get(`${URL_ADMIN}/treinamento/${this.form.curriculo_id}/editar`)
                .then(response => {
                    let data = response.data

                    if (data.treinamento) {
                        this.editando = true
                        Object.assign(this.form, data.treinamento)
                        this.form.listaVencimentos = data.listaVencimentos
                        this.form.nr_trinta_tres = data.nr_trinta_tres
                        this.form.nr_trinta_cinco = data.nr_trinta_cinco
                        this.form.nome = data.curriculo.nome
                    } else {
                        this.form.feedback_id = data.feedback_id
                        this.form.curriculo_id = curriculo_id
                        this.editando = false
                        this.form.nr_trinta_tres = data.nr_trinta_tres
                        this.form.nr_trinta_cinco = data.nr_trinta_cinco
                        this.form.vencimentos = []
                    }

                    if (data.feedback.exame) {
                        Object.assign(this.form.exame, data.feedback.exame)
                    } else {
                        this.form.exame.feedback_id = data.feedback.id
                    }


                    this.form.listaVencimentos = data.listaVencimentos

                    if (!this.form.nr_trinta_tres) {
                        //NR33
                        let index = _.findIndex(this.form.listaVencimentos, {'id': 7})
                        this.form.listaVencimentos.splice(index, 1)
                    }
                    if (!this.form.nr_trinta_cinco) {
                        //NR35
                        let index = _.findIndex(this.form.listaVencimentos, {'id': 6})
                        this.form.listaVencimentos.splice(index, 1)
                    }
                    this.preload = false
                })
                .catch(error => (this.preload = false))

        },

        salvar() {
            formReset()
            $('#janelaTreinamento :input:visible').trigger('blur')

            // if (this.nr_trinta_tres || this.nr_trinta_cinco) {
            if (this.nr_trinta_tres) {
                //NR33
                let nr33 = _.find(this.form.listaVencimentos, {'id': 7, 'fez_treinamento': false})
                if (nr33) {
                    nr33.fez_treinamento = false
                    mostraErro('', 'ATENÇÃO NR33 não pode ser vazio!')
                    return false
                }
            }

            if (this.nr_trinta_cinco) {
                let nr35 = _.find(this.form.listaVencimentos, {'id': 6, 'fez_treinamento': false})

                if (nr35) {
                    nr35.fez_treinamento = false
                    mostraErro('', 'ATENÇÃO NR35 não pode ser vazio!')
                    return false
                }
            }
            // }


            if ($('#janelaTreinamento :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os erros')
                return false
            }

            this.preload = true

            axios.post(`${URL_ADMIN}/treinamento`, this.form)
                .then(response => {
                    if (response.status === 201) {
                        this.preload = false
                        this.cadastrado = true
                        this.atualizar()
                    }
                }).catch(error => (this.preload = false))
        },

        salvarMassa() {
            formReset()
            $('#janelaTreinamentoMassa :input:visible').trigger('blur')


            if ($('#janelaTreinamentoMassa :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os erros')
                return false
            }

            this.preload = true;
            this.formMassa.selecionadosMassa = this.selecionadosMassa;

            axios.post(`${URL_ADMIN}/treinamento/salvar-massa`, this.formMassa)
                .then(response => {
                    if (response.status === 201) {
                        this.preload = false
                        this.cadastrado = true
                        this.atualizar()
                    }
                }).catch(error => (this.preload = false))
        },

        abriJanelaEnviar(obj) {
            this.formEnviar = _.cloneDeep(this.formEnviarDefault) //copia
            formReset()
            setupCampo()

            this.formEnviar.nome = obj.curriculo.nome
            this.formEnviar.titulo = `Enviar carteira etiqueta de ${this.formEnviar.nome}`
            this.formEnviar.email = obj.curriculo.email
            this.formEnviar.token = obj.treinamento.token
        },

        enviar() {
            $('#janelaEnviar :input:visible').trigger('blur')
            if ($('#janelaEnviar :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os campos marcados')
                return false
            }

            this.formEnviar.preload = true
            axios.post(`${URL_ADMIN}/treinamento/enviar-carteira`, this.formEnviar)
                .then(response => {
                    let data = response.data
                    this.formEnviar.preload = false
                    this.formEnviar.enviado = data.enviado
                })
                .catch(error => {
                    this.formEnviar.preload = false
                    this.formEnviar.enviado = false
                })
        },

        abriJanelaEnviarAviso() {
            this.formEnviarAviso = _.cloneDeep(this.formEnviarAvisoDefault) //copia
            formReset()
            setupCampo()
        },

        enviarAviso() {
            $('#janelaEnviarAviso :input:visible').trigger('blur')
            if ($('#janelaEnviarAviso :input:visible.is-invalid').length) {
                mostraErro('', 'Verificar os campos marcados')
                return false
            }

            this.formEnviarAviso.preload = true
            axios.post(`${URL_ADMIN}/treinamento/proximovencimento`, this.formEnviarAviso)
                .then(response => {
                    let data = response.data
                    this.formEnviarAviso.preload = false
                    this.formEnviarAviso.enviado = data.enviado
                })
                .catch(error => {
                    this.formEnviarAviso.preload = false
                    this.formEnviarAviso.enviado = false
                })
        },

        //GERAL
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior != this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = ''
                this.controle.dados.autocomplete_label = ''
                this.controle.dados.campoVaga = ''
            }
        },

        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id
            this.controle.dados.autocomplete_label = obj.label
            this.controle.dados.autocomplete_label_anterior = obj.label
        },

        resetaCampoCliente() {
            if (this.controle.dados.autocomplete_label_cliente_anterior != this.controle.dados.autocomplete_label_cliente) {
                this.controle.dados.autocomplete_label_cliente_anterior = ''
                this.controle.dados.autocomplete_label_cliente = ''
                this.controle.dados.campoCliente = ''
            }
        },

        selecionaCliente(obj) {
            this.controle.dados.campoCliente = obj.id
            this.controle.dados.autocomplete_label_cliente = obj.label
            this.controle.dados.autocomplete_label_cliente_anterior = obj.label
        },

        validaData() {
            if (this.form.data_aso.length >= 10) {
                let dataCorreta = moment(this.form.data_aso, 'DD/MM/YYYY')
                if (!dataCorreta.isValid()) {
                    mostraErro('', 'A data do ASO inserida é inválida')
                    this.form.data_aso = ''
                }
            }
        },

        listaVagas() {
            this.preload = true
            $.get(`${URL_PUBLICO}/lista-vagas`)
                .done((data) => {
                    this.preload = false
                    this.vagas = data.vagas
                })
                .fail((data) => {
                    this.preload = false
                })
        },

        listaAreasGeral() {
            this.preload = true
            $.get(`${URL_PUBLICO}/lista-areas`)
                .done((data) => {
                    this.preload = false
                    this.listaAreas = data.areas
                })
                .fail((data) => {
                    this.preload = false
                })
        },

        janelaConfirmar(id) {
            this.form.id = id
            this.apagado = false

            this.preload = false
        },

        carregou(dados) {
            this.lista = dados.itens
            this.selecionaTudo = this.tudoMarcado
            this.formMassa.listaVencimentos = dados.vencimentos
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
