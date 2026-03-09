import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import endereco from '../../../components/Endereco'
import telefone from '../../../components/Telefones'
import datepicker from '../../../components/DatePicker'
import classificar from '../../../components/Classificar'
const abrirModal = (selector) => {
    if (typeof $ === 'undefined') return
    $(selector).modal('show')
}

const fecharModal = (selector) => {
    if (typeof $ === 'undefined') return
    $(selector).modal('hide')
}



const app = createApp({
    components: {
        endereco,
        datepicker,
        telefone,
        classificar
    },
    data() {
        return {
            tituloJanela: 'Visualizando Curriculo',
            preloadAjax: false,
            editando: false,
            apagado: false,

            opened: [],
            cliente_id: 0,

            feedback: false,
            hash: `mastertag_${parseInt(Math.random() * 999999)}`,

            formClassificar: {
                curriculo_id: '',
                user_id: '',
                vaga_id: '',
                etapa: '',
                enviado_email: '',
                text_email: '',
                observacao: '',
                preenchido_por: '',
                status: '',

                simulado_id: 0,
                preload: false
            },

            formClassificarDefault: null,

            formDesclassificar: {
                curriculo_id: '',
                vaga_id: '',
                etapa: '',
                enviado_email: '',
                text_email: '',
                observacao: '',
                preenchido_por: '',
                status: '',

                simulado_id: 0,
                preload: false
            },

            formDesclassificarDefault: null,

            form: {
                id: '',
                curriculo: [],
                etapa_status: []
            },

            formDefault: null,

            campoNome: null,

            cadastrado: false,
            atualizado: false,

            lista: [],
            ufs: [],
            vagas: [],

            controle: {
                carregando: false,
                dados: {
                    caminho_autocomplete: `autocomplete/todas-vagas-ativas`,
                    autocomplete_label_anterior: '',
                    autocomplete_label: '',
                    pages: 20,
                    cliente_custom: '',
                    campoBusca: '',
                    campoVaga: '',
                    campoLido: '',
                    campoFiltro: '',
                    campoUf: '',
                    campoPcd: '',
                    campoCPF: '',
                    campoProvas: '',
                    campoNota: '',
                    campoStatus: '',
                    campoEtapa: '',
                    campoCliente: '',
                    filtroPeriodo: false,
                    periodo: ''
                }
            }
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        this.formClassificarDefault = _.cloneDeep(this.formClassificar) //copia
        this.formDesclassificarDefault = _.cloneDeep(this.formDesclassificar) //copia
        this.atualizar()
    },
    methods: {
        filtraProva() {
            if (this.controle.dados.campoProvas === 'nao' || this.controle.dados.campoProvas === 'andamento') {
                this.controle.dados.campoNota = ''
            }
            this.atualizar()
        },
        toggle(id) {
            const index = this.opened.indexOf(id)
            if (index > -1) {
                this.opened.splice(index, 1)
            } else {
                this.opened.push(id)
            }
        },

        resetaCampoCliente() {
            if (this.controle.dados.autocomplete_label_cliente_anterior !== this.controle.dados.autocomplete_label_cliente) {
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

        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id
            this.controle.dados.autocomplete_label = obj.label
            this.controle.dados.autocomplete_label_anterior = obj.label
            this.controle.carregando = true
            setTimeout(() => {
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }, 600)
        },
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = ''
                this.controle.dados.autocomplete_label = ''
                this.controle.dados.campoVaga = ''
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }
        },

        enviarNotificacao(fone, nome, curriculo_id, vaga_id, etapa_id, mensagem = null) {
            this.preloadAjax = true

            let dados = {}
            dados.fone = fone
            dados.nome = nome
            dados.curriculo_id = curriculo_id
            dados.vaga_id = vaga_id
            dados.etapa_id = etapa_id
            dados.mensagem = mensagem

            $.post(`${URL_ADMIN}/enviaNotificacao`, dados)
                .done((data) => {
                    fecharModal('#janelaWhatsApp')
                    mostraSucesso('', `Notificação para ${nome} enviada com sucesso!`)
                    this.preloadAjax = false
                    this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                })
                .fail((data) => {
                    this.preloadAjax = false
                })
        },

        atualizaClassificacao(resposta) {
            if (resposta.simulado_id > 0) {
                axios
                    .put(`${URL_ADMIN}/modificaStatus`, {
                        simulado_id: resposta.simulado_id,
                        status: resposta.status
                    })
                    .then((response) => {
                        let data = response.data
                        this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    })
            }
            setTimeout(() => {
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }, 100)
            this.formAlterar(resposta.id)
        },

        formAlterar(id) {
            this.form.id = id
            this.cadastrado = false
            this.atualizado = false
            this.editando = false

            this.preloadAjax = true
            // Object.assign(this.form_feedback, this.form_feedbackDefault);
            Object.assign(this.formClassificar, this.formClassificarDefault)
            Object.assign(this.formDesclassificar, this.formDesclassificarDefault)
            formReset()

            axios
                .get(`${URL_ADMIN}/curriculos/curriculos-selecionados/${id}/selecionado`)
                .then((response) => {
                    let data = response.data
                    Object.assign(this.form, data)
                    this.tituloJanela = `Visualizando Curriculo - ${data.curriculo.nome}`
                    this.editando = true
                    this.preloadAjax = false
                    setupCampo()
                })
                .catch((error) => {
                    this.preloadAjax = false
                })
        },
        alterar() {
            if (this.form_feedback.selecionado !== '' && this.form_feedback.selecionado !== 'nao') {
                if (this.form_feedback.vaga_id === '') {
                    valida_campo_vazio($('#vaga_modal_' + this.hash), 1)
                    $('#janelaCadastrar #vaga_modal_' + this.hash)
                        .focus()
                        .trigger('blur')
                    mostraErro('', 'O campo vaga não pode ficar vazio')
                    return false
                }
            }

            if (this.form_feedback.interesse) {
                if (this.form_feedback.cliente_id === '') {
                    valida_campo_vazio($('#cliente_modal_' + this.hash), 1)
                    $('#janelaCadastrar #cliente_modal_' + this.hash)
                        .focus()
                        .trigger('blur')
                    mostraErro('', 'O campo cliente não pode ficar vazio')
                    return false
                }
            }

            $('#janelaCadastrar :input:visible').trigger('blur')
            if ($('#janelaCadastrar :input:visible.is-invalid').length) {
                alert('Verificar os erros')
                return false
            }

            this.form_feedback._method = 'PUT'
            this.form_feedback.curriculos = this.form
            this.preloadAjax = true

            $.post(`${URL_ADMIN}/curriculos/recrutamentos/${this.form.id}`, this.form_feedback)
                .done((data) => {
                    this.preloadAjax = false
                    this.atualizado = true
                    this.atualizar()
                })
                .fail((data) => {
                    this.preloadAjax = false
                })
        },

        listaVagas() {
            this.preloadAjax = true
            axios
                .get(`${URL_PUBLICO}/lista-vagas`)
                .then((response) => {
                    let data = response.data
                    this.preloadAjax = false
                    this.vagas = data.vagas
                })
                .catch((error) => {
                    this.preloadAjax = false
                })
        },

        carregou(dados) {
            this.lista = dados.itens
            this.cliente_id = dados.usuario_cliente_id
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        }
    }
})
registerGlobals(app)
app.mount('#app')
