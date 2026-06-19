import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import endereco from '../../../components/Endereco'
import datepicker from '../../../components/DatePicker'
import DadosPessoais from '../../../components/entrevistas/DadosPessoaisTexto'
import ExportacaoMixin from '../../../mixins/Exportacoes'
import Utils from '../../../mixins/Utils'
import Configuracoes from '../../../mixins/Configuracoes'
const app = createApp({
    mixins: [ExportacaoMixin, Utils, Configuracoes],
    components: {
        endereco,
        datepicker,
        DadosPessoais
    },
    data() {
        return {
            tituloJanela: 'Parecer Entrevista RH',
            preload: false,
            editando: false,
            apagado: false,
            cadastrado: false,
            cadastrando: false,
            atualizado: false,
            visualizar: false,
            preloadExportacao: false,
            preloadWhatsapp: false,
            preloadWhatsappModal: false,
            whatsappModalErro: '',
            tituloWhatsappModal: 'Enviar WhatsApp',
            whatsappModalCandidatoNome: '',
            whatsappModalForm: {
                curriculo: {},
                tel_principal: null,
                vaga_aberta_municipio: ''
            },
            whatsappModalMensagem: '',
            parecerRotaWhatsappId: null,
            whatsappModalEnviadoEm: null,
            whatsappModalEnviadoPor: '',

            telefone_whatsapp: '',
            telefone_whatsapp_tipo: 'whatsapp',
            possui_whatsapp_cadastrado: false,
            telefone_contato_tipo: '',

            urlExportacao: `${URL_ADMIN}/entrevistas/parecer-rota/export`,

            hash: `mybp_${parseInt(Math.random() * 999999)}`,

            todos_municipios: `autocomplete/todos-municipios`,

            cliente_id: '',

            URL_ADMIN,
            selecionados: [],
            selecionaTudo: false,
            dropdownAbertoKey: null,

            colunasTabela: {
                pcd: true,
                cliente: false,
                parecer_rh: true,
                tecnica_nota: true,
                teste_pratico_nota: true
            },

            form: {
                id: '',

                vaga_id: '',
                autocomplete_label_vaga_modal: '',
                autocomplete_label_vaga_modal_anterior: '',

                cliente_id: '',
                autocomplete_label_cliente_modal: '',
                autocomplete_label_cliente_modal_anterior: '',

                curriculo: {
                    nome: '',
                    nascimento: '',
                    municipio_id: '',
                    autocomplete_label_municipio_modal: '',
                    autocomplete_label_municipio_modal_anterior: ''
                },

                parecer_rota: {
                    feedback_id: '',
                    curriculo_id: '',
                    tem_rota: '',
                    qual: '',
                    pega_onibus: '',
                    pega_onibus_qual_ponto: '',
                    vale_transporte: '',
                    rota_disponivel_turno_a: '',
                    rota_disponivel_turno_b: '',
                    rota_disponivel_turno_c: '',
                    rota_disponivel_turno_o: '',
                    rota_disponivel_outros: '',
                    rota_atende: '',
                    rota_tipo: '',
                    quem_entrevistou: '',
                    bairro_rota: '',
                    ponto_referencia_rota: '',
                    bairro_residencia: '',
                    ponto_referencia_residencia: '',
                    observacao: '',
                    whatsapp_enviado_em: null
                }
            },

            formDefault: null,

            lista: [],
            vagas: [],
            opened: [],

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
                    campoBusca: '',
                    campoVaga: '',
                    campoLido: '',
                    campoFiltro: '',
                    campoPcd: '',
                    campoRota: '',
                    campoCliente: '',
                    campoUf: '',

                    filtroPeriodo: false,
                    periodo: '',
                    page: 1
                }
            }
        }
    },
    watch: {
        'controle.dados': {
            handler() {
                if (this._syncUrlTimer) clearTimeout(this._syncUrlTimer)
                this._syncUrlTimer = setTimeout(() => this.syncUrlFiltros(), 400)
            },
            deep: true
        }
    },
    computed: {
        comRota() {
            return this.lista.filter((item) => {
                return item.parecer_rota
            })
        },
        paramsExport() {
            let dados = this.controle.dados
            dados.selecionados = this.selecionados
            return dados
        },
        tudoMarcado() {
            if (this.comRota.length === 0) {
                this.selecionaTudo = false
                return false
            }

            const resultado = this.comRota.every((item) => this.selecionados.indexOf(item.id) >= 0)
            this.selecionaTudo = resultado
            return resultado
        },
        podeEnviarWhatsapp() {
            if (!this.whatsappLiberado || !this.parecerRotaWhatsappId) {
                return false
            }

            if (this.telefone_whatsapp_tipo !== 'whatsapp') {
                return false
            }

            const telefone = (this.telefone_whatsapp || '').replace(/\D/g, '')

            return telefone.length >= 10
        },
        mensagemWhatsappPreviewHtml() {
            return this.formatarMensagemWhatsappPreview(this.whatsappModalMensagem)
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form) //copia
        document.addEventListener('click', this.onClickOutside)
        this.urlParamGet()
        this.usuarioAutenticado()
        this.$nextTick(() => {
            const page = this.controle.dados.page
            if (this.$refs.componente && page >= 1) {
                this.$refs.componente.atual = page
            }
            this.atualizar()
        })
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },
    methods: {
        /***Campos de Filtros ****/
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = ''
                this.controle.dados.autocomplete_label = ''
                this.controle.dados.campoVaga = ''
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }
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
        resetaCampoCliente() {
            if (this.controle.dados.autocomplete_label_cliente_anterior !== this.controle.dados.autocomplete_label_cliente) {
                this.controle.dados.autocomplete_label_cliente_anterior = ''
                this.controle.dados.autocomplete_label_cliente = ''
                this.controle.dados.campoCliente = ''
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }
        },
        selecionaCliente(obj) {
            this.controle.dados.campoCliente = obj.id
            this.controle.dados.autocomplete_label_cliente = obj.label
            this.controle.dados.autocomplete_label_cliente_anterior = obj.label
            this.controle.carregando = true
            this.controle.carregando = true
            setTimeout(() => {
                this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
            }, 600)
        },
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo
            if (this.selecionaTudo) {
                this.comRota.map((item) => {
                    let id = item.id
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id)
                    }
                })
            } else {
                this.comRota.map((item) => {
                    let id = item.id
                    let index = this.selecionados.indexOf(id)
                    if (index >= 0) {
                        this.selecionados.splice(index, 1)
                    }
                })
            }
        },

        formEntrevistar(id) {
            this.cadastrado = false
            this.atualizado = false
            this.cadastrando = false
            this.visualizar = false
            this.editando = false

            this.preload = true
            this.form = _.cloneDeep(this.formDefault)
            this.form.id = id

            formReset()
            axios
                .get(`${URL_ADMIN}/entrevistas/parecer-rota/${id}/editar`)
                .then((response) => {
                    let data = response.data
                    Object.assign(this.form, data)

                    //Se não tiver parecer_rota
                    this.form.parecer_rota = data.parecer_rota ? data.parecer_rota : _.cloneDeep(this.formDefault.parecer_rota)
                    this.form.parecer_rota.rota_tipo = lower(data.parecer_rh.tipo_entrevista)

                    this.popularTelefoneWhatsapp(data)

                    this.tituloJanela = `#${data.feedback.id} Entrevista - ${data.feedback.curriculo.nome}`
                    this.cadastrando = true
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        cadastrar() {
            $('#janelaParecerEntrevista :input:visible').trigger('blur')
            if ($('#janelaParecerEntrevista :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true

            this.form.parecer_rota.feedback_id = this.form.id
            this.form.parecer_rota.curriculo_id = this.form.curriculo_id

            axios
                .post(`${URL_ADMIN}/entrevistas/parecer-rota/`, this.form.parecer_rota)
                .then((response) => {
                    let data = response.data
                    Object.assign(this.form.parecer_rota, data.data || {})
                    this.editando = true
                    mostraSucesso('', 'Entrevista salva com sucesso!')
                    this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        alterar() {
            $('#janelaParecerEntrevista :input:visible').trigger('blur')
            if ($('#janelaParecerEntrevista :input:visible.is-invalid').length) {
                mostraErro('', 'Verifique os campos marcados')
                return false
            }

            this.preload = true

            axios
                .put(`${URL_ADMIN}/entrevistas/parecer-rota/${this.form.parecer_rota.id}`, this.form.parecer_rota)
                .then((response) => {
                    let data = response.data
                    Object.assign(this.form.parecer_rota, data.data || {})
                    mostraSucesso('', 'Entrevista salva com sucesso!')
                    this.$refs.janelaParecerEntrevista?.fecharModal()
                    this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                    this.preload = false
                })
                .catch((error) => {
                    this.preload = false
                })
        },

        janelaConfirmar(id) {
            this.form.id = id
            this.apagado = false
            this.preload = false
        },

        usuarioAutenticado() {
            this.controle.carregando = true
            axios
                .get(`${URL_ADMIN}/usuario/autenticado/`)
                .then((response) => {
                    let data = response.data
                    this.cliente_id = data.cliente_id
                    this.colunasTabela.cliente = this.cliente_id === 0
                    this.controle.dados.campoCliente = this.cliente_id !== 0 ? this.cliente_id : this.controle.dados.campoCliente
                })
                .catch((error) => {
                    this.preload = false
                })
        },
        carregou(dados) {
            this.lista = dados.itens
            this.selecionaTudo = this.tudoMarcado
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.syncUrlFiltros()
            this.$refs && this && this && this.$refs && this.$refs.componente && (this.$refs.componente.atual = 1)
            this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
        },
        urlParamGet() {
            const urlParams = new URLSearchParams(window.location.search)
            if (urlParams.get('page')) {
                const p = parseInt(urlParams.get('page'), 10)
                if (p >= 1) this.controle.dados.page = p
            }
            if (urlParams.get('pages')) {
                const pp = parseInt(urlParams.get('pages'), 10)
                if ([20, 50, 100].indexOf(pp) >= 0) this.controle.dados.pages = pp
            }
            if (urlParams.get('campoBusca')) this.controle.dados.campoBusca = urlParams.get('campoBusca')
            if (urlParams.get('campoCPF')) this.controle.dados.campoCPF = urlParams.get('campoCPF')
            if (urlParams.get('campoCliente')) this.controle.dados.campoCliente = urlParams.get('campoCliente')
            if (urlParams.get('campoVaga')) this.controle.dados.campoVaga = urlParams.get('campoVaga')
            if (urlParams.get('campoUf')) this.controle.dados.campoUf = urlParams.get('campoUf')
            if (urlParams.get('campoRota')) this.controle.dados.campoRota = urlParams.get('campoRota')
            const fp = urlParams.get('filtroPeriodo')
            if (fp === '1' || fp === 'true') this.controle.dados.filtroPeriodo = true
            if (urlParams.get('periodo')) this.controle.dados.periodo = urlParams.get('periodo')
        },
        syncUrlFiltros() {
            const d = this.controle.dados
            const atual = this.$refs.componente && this.$refs.componente.atual ? this.$refs.componente.atual : 1
            const params = {}
            if (atual > 1) params.page = atual
            if (d.pages && d.pages !== 20 && d.pages !== '20') params.pages = d.pages
            if (d.campoBusca) params.campoBusca = d.campoBusca
            if (d.campoCPF) params.campoCPF = d.campoCPF
            if (d.campoCliente) params.campoCliente = d.campoCliente
            if (d.campoVaga) params.campoVaga = d.campoVaga
            if (d.campoUf) params.campoUf = d.campoUf
            if (d.campoRota) params.campoRota = d.campoRota
            if (d.filtroPeriodo) params.filtroPeriodo = 1
            if (d.filtroPeriodo && d.periodo) params.periodo = d.periodo
            const qs = new URLSearchParams(params).toString()
            const url = qs ? `${window.location.pathname}?${qs}` : window.location.pathname
            window.history.replaceState({}, '', url)
        },
        popularTelefoneWhatsapp(data, formDestino = null) {
            const form = formDestino || this.form
            const telWhatsapp = data.telefone_whatsapp_candidato || null
            const telPrincipal = data.tel_principal || null

            if (telWhatsapp?.numero) {
                this.telefone_whatsapp = telWhatsapp.numero
                this.telefone_whatsapp_tipo = 'whatsapp'
                this.possui_whatsapp_cadastrado = true
                this.telefone_contato_tipo = telWhatsapp.tipo_text || 'WhatsApp'
            } else if (telPrincipal?.tipo === 'whatsapp' && telPrincipal?.numero) {
                this.telefone_whatsapp = telPrincipal.numero
                this.telefone_whatsapp_tipo = 'whatsapp'
                this.possui_whatsapp_cadastrado = true
                this.telefone_contato_tipo = telPrincipal.tipo_text || 'WhatsApp'
            } else {
                this.telefone_whatsapp = telPrincipal?.numero || ''
                this.telefone_whatsapp_tipo = 'whatsapp'
                this.possui_whatsapp_cadastrado = false
                this.telefone_contato_tipo = telPrincipal?.tipo_text || telPrincipal?.tipo || 'Não informado'
            }

            if (telPrincipal) {
                form.tel_principal = telPrincipal
            }
        },
        montarWhatsappModalForm(entrevista) {
            return {
                curriculo: entrevista?.curriculo ? { ...entrevista.curriculo } : {},
                tel_principal: entrevista?.tel_principal || null,
                vaga_aberta_municipio: entrevista?.vaga_aberta_municipio || ''
            }
        },
        formatarDataWhatsapp(data) {
            if (!data) {
                return ''
            }

            const parsed = new Date(data)
            if (Number.isNaN(parsed.getTime())) {
                return data
            }

            return parsed.toLocaleString('pt-BR')
        },
        formatarMensagemWhatsappPreview(mensagem) {
            if (!mensagem) {
                return ''
            }

            const escaped = mensagem
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')

            return escaped
                .replace(/\*([^*\n]+)\*/g, '<strong>$1</strong>')
                .replace(/\n/g, '<br>')
        },
        abrirModalWhatsapp(entrevista) {
            if (!entrevista?.parecer_rota?.id) {
                mostraErro('', 'Parecer de rota não encontrado')
                return false
            }

            this.fecharDropdown()
            this.whatsappModalErro = ''
            this.whatsappModalMensagem = ''
            this.parecerRotaWhatsappId = entrevista.parecer_rota.id
            this.whatsappModalForm = this.montarWhatsappModalForm(entrevista)
            this.whatsappModalCandidatoNome = entrevista.curriculo?.nome || 'Candidato'
            this.tituloWhatsappModal = `#${entrevista.id} — Enviar WhatsApp`
            this.whatsappModalEnviadoEm = entrevista.parecer_rota.whatsapp_enviado_em || null
            this.whatsappModalEnviadoPor = entrevista.parecer_rota.quem_enviou_whatsapp?.nome || ''
            this.telefone_whatsapp = ''
            this.telefone_whatsapp_tipo = 'whatsapp'
            this.possui_whatsapp_cadastrado = false
            this.telefone_contato_tipo = ''
            this.preloadWhatsappModal = true
            this.$refs.janelaWhatsappParecerRota?.abrirModal()

            axios
                .get(`${URL_ADMIN}/entrevistas/parecer-rota/${entrevista.parecer_rota.id}/preview-whatsapp`)
                .then((response) => {
                    const data = response.data
                    this.whatsappModalMensagem = data.mensagem || ''
                    if (data.candidato) {
                        this.whatsappModalForm = {
                            ...this.whatsappModalForm,
                            ...data.candidato,
                            curriculo: data.candidato.curriculo || this.whatsappModalForm.curriculo
                        }
                        this.whatsappModalCandidatoNome = data.candidato.curriculo?.nome || this.whatsappModalCandidatoNome
                    }
                    this.whatsappModalEnviadoEm = data.parecer_rota?.whatsapp_enviado_em || null
                    this.whatsappModalEnviadoPor = data.parecer_rota?.quem_enviou_whatsapp?.nome || ''
                    this.popularTelefoneWhatsapp(data, this.whatsappModalForm)
                    this.preloadWhatsappModal = false
                })
                .catch((error) => {
                    this.whatsappModalErro = error.response?.data?.msg || 'Erro ao carregar pré-visualização'
                    this.preloadWhatsappModal = false
                })
        },
        enviarWhatsapp() {
            if (!this.podeEnviarWhatsapp) {
                mostraErro('', 'Verifique o telefone WhatsApp antes de enviar')
                return false
            }

            if (this.whatsappModalEnviadoEm) {
                this.$refs.janelaConfirmarWhatsapp?.abrirModal()
                return false
            }

            this.executarEnvioWhatsapp()
        },
        executarEnvioWhatsapp() {
            if (!this.podeEnviarWhatsapp || this.preloadWhatsapp) {
                return false
            }

            this.preloadWhatsapp = true

            axios
                .post(`${URL_ADMIN}/entrevistas/parecer-rota/${this.parecerRotaWhatsappId}/enviar-whatsapp`, {
                    telefone: this.telefone_whatsapp,
                    tipo: this.telefone_whatsapp_tipo
                })
                .then((response) => {
                    const data = response.data
                    const parecerAtualizado = data.data || {}
                    this.whatsappModalEnviadoEm = parecerAtualizado.whatsapp_enviado_em || null
                    this.whatsappModalEnviadoPor = parecerAtualizado.quem_enviou_whatsapp?.nome || ''
                    mostraSucesso('', data.msg || 'WhatsApp enfileirado com sucesso!')
                    this.preloadWhatsapp = false
                    this.$refs.janelaConfirmarWhatsapp?.fecharModal()
                    this.$refs.janelaWhatsappParecerRota?.fecharModal()
                    this && this.$refs && this.$refs.componente && this.$refs.componente.buscar ? this.$refs.componente.buscar() : null
                })
                .catch((error) => {
                    const msg = error.response?.data?.msg || 'Erro ao enviar WhatsApp'
                    mostraErro('', msg)
                    this.preloadWhatsapp = false
                })
        },
        toggleDropdown(itemId) {
            if (!itemId) {
                return
            }
            const key = `parecer-rota:${itemId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(itemId) {
            return this.dropdownAbertoKey === `parecer-rota:${itemId}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        onClickOutside(event) {
            if (event && event.target && event.target.closest && event.target.closest('.dropdown')) {
                return
            }
            this.dropdownAbertoKey = null
        },
        getStatusRotaLabel(entrevista) {
            if (!entrevista.parecer_rota) {
                return 'Aguardando'
            }
            if (entrevista.parecer_rota.rota_atende === true) {
                return 'Rota atende'
            }
            if (entrevista.parecer_rota.rota_atende === false) {
                return 'Rota não atende'
            }
            return 'Não informado'
        },
        getCardStatusClass(entrevista) {
            if (!entrevista.parecer_rota) {
                return 'card-status-pendente'
            }
            if (entrevista.parecer_rota.rota_atende === true) {
                return 'card-status-rota-sim'
            }
            if (entrevista.parecer_rota.rota_atende === false) {
                return 'card-status-rota-nao'
            }
            return 'card-status-rota-info'
        },
        getStatusBadgeClass(entrevista) {
            if (!entrevista.parecer_rota) {
                return 'status-pendente'
            }
            if (entrevista.parecer_rota.rota_atende === true) {
                return 'status-admitido'
            }
            if (entrevista.parecer_rota.rota_atende === false) {
                return 'status-demitido'
            }
            return 'status-processo'
        },
        getWhatsappEnviadoLabel(entrevista) {
            const parecer = entrevista.parecer_rota
            if (!parecer?.whatsapp_enviado_em) {
                return 'Não enviado'
            }

            const data = this.formatarDataWhatsapp(parecer.whatsapp_enviado_em)
            const por = parecer.quem_enviou_whatsapp?.nome

            return por ? `${data} — ${por}` : data
        },
        gerarPdf(entrevista) {
            if (!entrevista.parecer_rota?.id) {
                return
            }

            const form = document.createElement('form')
            form.method = 'POST'
            form.action = `${URL_ADMIN}/entrevistas/parecer-rota/ficha_pdf`
            form.target = '_blank'

            const csrfMeta = document.querySelector('meta[name="csrf-token"]')
            if (csrfMeta) {
                const csrf = document.createElement('input')
                csrf.type = 'hidden'
                csrf.name = '_token'
                csrf.value = csrfMeta.getAttribute('content')
                form.appendChild(csrf)
            }

            const idInput = document.createElement('input')
            idInput.type = 'hidden'
            idInput.name = 'id'
            idInput.value = entrevista.parecer_rota.id
            form.appendChild(idInput)

            document.body.appendChild(form)
            form.submit()
            document.body.removeChild(form)
        }
    }
})

registerGlobals(app)
app.mount('#app')
