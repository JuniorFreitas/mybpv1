import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import upload from '../../../components/Upload'
import validacoes from '../../../mixins/Validacoes'
import configuracoes from '../../../mixins/Configuracoes'
import FiltroListagem from '../../../components/ui/FiltroListagem'
import ComboboxAutoComplete from '../../../components/ComboboxAutoComplete'
import MybpCardCampo from '../../../components/ui/MybpCardCampo.vue'
import MybpStatusBadge from '../../../components/ui/MybpStatusBadge.vue'
import {
    lerFiltrosDaUrl,
    lerPaginacaoDaUrl,
    sincronizarFiltrosNaUrl,
    criarWatchQueryParams,
    aplicarPaginaInicialListagem,
    buscarListagem,
    limparFiltrosListagem,
    montarExtrasPaginacao
} from '../../../utils/listagemQueryParams'

const CAMPOS_FILTRO_URL = ['campoBusca', 'campoVaga', 'campoCliente', 'campoUf', 'status', 'statusDocumento']
const PAGES_DEFAULT = 20
const UFS = [
    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
    'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
    'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
]

const app = createApp({
    mixins: [validacoes, configuracoes],
    components: {
        upload,
        FiltroListagem,
        ComboboxAutoComplete,
        MybpCardCampo,
        MybpStatusBadge
    },
    data() {
        return {
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

            hash: `mastertag_${parseInt(Math.random() * 999999)}`,

            todos_municipios: `autocomplete/todos-municipios`,

            URL_ADMIN,
            selecionados: [],
            selecionaTudo: false,
            dropdownAbertoKey: null,

            form: {
                id: '',
                curriculo_id: '',
                candidato_nome: '',
                candidato_cpf_mascarado: '',
                candidato_email: '',
                candidato_uf: '',
                contato_telefone: '',
                cliente_nome: '',
                cargo_nome: '',
                vaga_local: '',
                qnt_anexos: 0,
                situacao_colaborador: 'em_processo',
                finalizado: false,
                quem_finalizou: null,
                data_finalizacao: null,
                docs_curriculo_pre_adm: []
            },
            formDefault: null,

            formEmail: {
                id: '',
                email: '',
                curriculo_id: '',
                observacao: '',
                temwhatsapp: false,
                tel_principal: null,
                envia_whatsapp: false,
                numero_telefone: '',
                candidato_nome: '',
                candidato_cpf_mascarado: '',
                candidato_email: '',
                candidato_uf: '',
                contato_telefone: '',
                cliente_nome: '',
                cargo_nome: '',
                vaga_local: '',
                qnt_anexos: 0,
                situacao_colaborador: 'em_processo',
                finalizado: false
            },
            formEmailDefault: null,

            formFinalizar: {
                feedback_id: '',
                empresa_exame_id: '',
                encaminhado_exame_data: '',
                pcmso_id: '',
                envia_email: true,
                envia_whatsapp: true
            },
            formFinalizarDefault: {
                feedback_id: '',
                empresa_exame_id: '',
                encaminhado_exame_data: '',
                pcmso_id: '',
                envia_email: true,
                envia_whatsapp: true
            },

            lista: [],
            listaPcmsos: [],
            listaEmpresasExames: [],
            dadosFinalizar: {},
            vagas: [],
            areasEtiquetas: [],

            previewWhatsappAberto: false,
            previewWhatsappTipo: '',
            previewWhatsappContexto: {},

            controle: {
                carregando: false,
                dados: {
                    caminho_autocomplete: `autocomplete/todas-vagas-ativas`,
                    autocomplete_label_anterior: '',
                    autocomplete_label: '',
                    caminho_cliente_autocomplete: `autocomplete/todos-clientes-ativos`,
                    autocomplete_label_cliente_anterior: '',
                    autocomplete_label_cliente: '',
                    pages: PAGES_DEFAULT,
                    cliente_custom: '',
                    campoBusca: '',
                    campoVaga: '',
                    campoLido: '',
                    campoFiltro: '',
                    campoPcd: '',
                    campoCliente: '',
                    campoUf: '',
                    status: 'em_processo',
                    statusDocumento: ''
                }
            }
        }
    },
    computed: {
        clinicaSelecionadaEmail() {
            const clinica = this.listaEmpresasExames.find(
                (item) => item.id === this.formFinalizar.empresa_exame_id
            )
            return clinica?.email || ''
        },
        temFiltrosAtivos() {
            const d = this.controle.dados
            return !!(
                d.campoBusca ||
                d.campoVaga ||
                d.campoCliente ||
                d.campoUf ||
                (d.status && d.status !== 'em_processo') ||
                d.statusDocumento
            )
        },
        filtroStatusOpcoes() {
            return [
                { value: 'em_processo', label: 'Em processo' },
                { value: 'admitidos', label: 'Admitidos' },
                { value: 'demitidos', label: 'Demitidos' }
            ]
        },
        filtroStatusDocumentoOpcoes() {
            return [
                { value: '', label: 'Todos' },
                { value: 'pendente', label: 'Pendentes' },
                { value: 'concluido', label: 'Concluídos' }
            ]
        },
        filtroUfOpcoes() {
            return [{ value: '', label: 'Todos os estados' }, ...UFS.map((uf) => ({ value: uf, label: uf }))]
        },
        filtroPagesOpcoes() {
            return [
                { value: 20, label: '20' },
                { value: 50, label: '50' },
                { value: 100, label: '100' }
            ]
        },
        comAdm() {
            return this.lista.filter((item) => item.admissao)
        },
        tudoMarcado() {
            const totalItens = this.comAdm.length
            if (totalItens === 0) {
                return false
            }
            let totalEncontrado = 0
            this.comAdm.forEach((item) => {
                if (this.selecionados.indexOf(item.curriculo_id) >= 0) {
                    totalEncontrado++
                }
            })
            const resultado = totalItens === totalEncontrado
            this.selecionaTudo = resultado
            return resultado
        },
        dataHoje() {
            return new Date(Date.now()).toLocaleString().split(',')[0]
        }
    },
    watch: {
        'controle.dados': criarWatchQueryParams(CAMPOS_FILTRO_URL, { pagesDefault: PAGES_DEFAULT })
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form)
        this.formEmailDefault = _.cloneDeep(this.formEmail)

        lerFiltrosDaUrl(this.controle.dados, CAMPOS_FILTRO_URL)
        if (!this.controle.dados.status) {
            this.controle.dados.status = 'em_processo'
        }

        const paginaInicial = lerPaginacaoDaUrl(this.controle.dados, { pagesDefault: PAGES_DEFAULT })
        this.$nextTick(() => {
            aplicarPaginaInicialListagem(this, paginaInicial)
            buscarListagem(this, { resetPagina: false })
        })

        this.listaVagas()
        document.addEventListener('click', this.onClickOutside)
    },
    beforeUnmount() {
        document.removeEventListener('click', this.onClickOutside)
    },
    methods: {
        textoCampo(valor) {
            const texto = String(valor ?? '').trim()
            return texto !== '' ? texto : 'Não informado'
        },
        nomeCandidato(item) {
            return this.textoCampo(item?.candidato_nome)
        },
        cpfExibicao(item) {
            return item?.candidato_cpf_mascarado || ''
        },
        clienteExibicao(item) {
            return this.textoCampo(item?.cliente_nome)
        },
        anexosExibicao(item) {
            const qnt = Number(item?.qnt_anexos || 0)
            return `${qnt} anexo(s)`
        },
        chaveSituacaoColaborador(item) {
            const situacao = item?.situacao_colaborador || 'em_processo'
            if (situacao === 'admitido') return 'aprovado'
            if (situacao === 'demitido') return 'reprovado'
            return 'aberto'
        },
        textoSituacaoColaborador(item) {
            const situacao = item?.situacao_colaborador || 'em_processo'
            if (situacao === 'admitido') return 'Admitido'
            if (situacao === 'demitido') return 'Demitido'
            return 'Em processo'
        },
        chaveStatusDocumento(item) {
            return item?.finalizado ? 'aprovado' : 'pendente'
        },
        textoStatusDocumento(item) {
            return item?.finalizado ? 'Concluídos' : 'Pendentes'
        },
        classeCorpoStatus(item) {
            return item?.finalizado ? 'mybp-card-corpo--aprovado' : 'mybp-card-corpo--pendente'
        },
        onSubmitFiltro() {
            this.atualizar()
        },
        onSelectFiltro() {
            this.atualizar()
        },
        limparFiltros() {
            limparFiltrosListagem(this.controle.dados, CAMPOS_FILTRO_URL)
            this.controle.dados.status = 'em_processo'
            this.controle.dados.statusDocumento = ''
            this.controle.dados.autocomplete_label = ''
            this.controle.dados.autocomplete_label_anterior = ''
            this.controle.dados.autocomplete_label_cliente = ''
            this.controle.dados.autocomplete_label_cliente_anterior = ''
            this.fecharOutrosComboboxes(null)
            this.atualizar()
        },
        fecharOutrosComboboxes(manter) {
            const combos = [
                ['filtro-status', 'comboFiltroStatus'],
                ['filtro-status-documento', 'comboFiltroStatusDocumento'],
                ['filtro-uf', 'comboFiltroUf'],
                ['filtro-pages', 'comboFiltroPages']
            ]
            combos.forEach(([id, refName]) => {
                if (id !== manter && this.$refs[refName]?.close) {
                    this.$refs[refName].close()
                }
            })
        },
        syncUrlFiltros() {
            sincronizarFiltrosNaUrl(
                this.controle.dados,
                CAMPOS_FILTRO_URL,
                montarExtrasPaginacao(this, { pagesDefault: PAGES_DEFAULT })
            )
        },
        toggleDropdown(itemId) {
            if (!itemId) return
            const key = `preadm:${itemId}`
            this.dropdownAbertoKey = this.dropdownAbertoKey === key ? null : key
        },
        isDropdownOpen(itemId) {
            return this.dropdownAbertoKey === `preadm:${itemId}`
        },
        fecharDropdown() {
            this.dropdownAbertoKey = null
        },
        onClickOutside(event) {
            if (event?.target?.closest?.('.dropdown') || event?.target?.closest?.('.mybp-combobox-wrap')) {
                return
            }
            this.dropdownAbertoKey = null
            this.fecharOutrosComboboxes(null)
        },
        selecionaTodos() {
            this.selecionaTudo = !this.selecionaTudo
            if (this.selecionaTudo) {
                this.comAdm.forEach((item) => {
                    const id = item.curriculo_id
                    if (this.selecionados.indexOf(id) === -1) {
                        this.selecionados.push(id)
                    }
                })
            } else {
                this.comAdm.forEach((item) => {
                    const id = item.curriculo_id
                    const index = this.selecionados.indexOf(id)
                    if (index >= 0) {
                        this.selecionados.splice(index, 1)
                    }
                })
            }
        },
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = ''
                this.controle.dados.autocomplete_label = ''
                this.controle.dados.campoVaga = ''
            }
        },
        resetaCampoCliente() {
            if (
                this.controle.dados.autocomplete_label_cliente_anterior !==
                this.controle.dados.autocomplete_label_cliente
            ) {
                this.controle.dados.autocomplete_label_cliente_anterior = ''
                this.controle.dados.autocomplete_label_cliente = ''
                this.controle.dados.campoCliente = ''
            }
        },
        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id
            this.controle.dados.autocomplete_label = obj.label
            this.controle.dados.autocomplete_label_anterior = obj.label
            this.atualizar()
        },
        selecionaCliente(obj) {
            this.controle.dados.campoCliente = obj.id
            this.controle.dados.autocomplete_label_cliente = obj.label
            this.controle.dados.autocomplete_label_cliente_anterior = obj.label
            this.atualizar()
        },
        formVisualizar(id) {
            this.form.id = id
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.visualizar = true
            this.preload = true
            Object.assign(this.form, this.formDefault)
            formReset()
            axios
                .get(`${URL_ADMIN}/preadmissao/${id}`)
                .then((response) => {
                    const data = response.data || {}
                    Object.assign(this.form, this.formDefault, data)
                    this.form.docs_curriculo_pre_adm = Array.isArray(data.docs_curriculo_pre_adm)
                        ? data.docs_curriculo_pre_adm
                        : []
                    this.tituloJanela = `Pré-admissão - ${this.textoCampo(data.candidato_nome)}`
                    this.preload = false
                })
                .catch(() => {
                    this.preload = false
                })
        },
        abrirFormFinalizar(id) {
            this.preloadFinalizar = true
            this.formFinalizar = _.cloneDeep(this.formFinalizarDefault)
            this.dadosFinalizar = {}
            formReset()
            axios
                .get(`${URL_ADMIN}/preadmissao/finalizar/${id}`)
                .then(({ data }) => {
                    this.dadosFinalizar = data.dados || {}
                    this.listaPcmsos = data.pcmsos || []
                    this.listaEmpresasExames = data.empresas_exames || []
                    this.tituloJanelaFinalizar = `Finalizar Pré-admissão - ${this.textoCampo(this.dadosFinalizar.candidato_nome)}`
                    this.preloadFinalizar = false
                })
                .catch(() => {
                    this.preloadFinalizar = false
                })
        },
        formEnviarEmail(id) {
            this.formEmail.id = id
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.preload = true
            Object.assign(this.formEmail, this.formEmailDefault)
            formReset()
            axios
                .get(`${URL_ADMIN}/preadmissao/editar/${id}`)
                .then((response) => {
                    const data = response.data || {}
                    Object.assign(this.formEmail, this.formEmailDefault, data)
                    this.formEmail.id = data.id
                    this.formEmail.curriculo_id = data.curriculo_id
                    this.formEmail.email = data.candidato_email || ''
                    this.formEmail.temwhatsapp = data.tel_principal?.tipo === 'whatsapp'
                    this.formEmail.tel_principal = data.tel_principal || null
                    this.formEmail.envia_whatsapp = data.tel_principal?.tipo === 'whatsapp'
                    this.formEmail.numero_telefone = data.tel_principal?.sonumero || ''
                    this.tituloJanela = `Reenvio de E-mail - ${this.textoCampo(data.candidato_nome)}`
                    this.preload = false
                })
                .catch(() => {
                    this.preload = false
                })
        },
        enviarEmail() {
            this.formEmail.email = this.formEmail.email.toLowerCase().trim()
            this.validaBlur()
            if ($(`#janelaEnviarEmail :input:visible.is-invalid`).length) {
                this.mostraErro('', 'Preencha todos os campos obrigatórios')
                return false
            }
            if (this.formEmail.email === this.email_padrao.toLowerCase().trim()) {
                this.mostraErro('', `Preencha com um e-mail diferente de ${this.email_padrao}`)
                return false
            }
            this.preload = true
            formReset()
            axios
                .post(`${URL_ADMIN}/preadmissao/enviar-email`, this.formEmail)
                .then((response) => {
                    if (response.status === 201) {
                        this.$refs.janelaEnviarEmail?.fecharModal()
                        this.mostraSucesso('Envio do e-mail concluído com sucesso!')
                    }
                    this.preload = false
                })
                .catch(() => {
                    this.preload = false
                })
        },
        finalizarEncaminhar(id) {
            this.validaBlur()
            $('#janelaFinalizar :input:visible').trigger('blur')
            if ($('#janelaFinalizar :input:visible.is-invalid').length) {
                mostraErro('', 'Preencha todos os campos obrigatórios')
                return false
            }
            this.preloadFinalizar = true
            this.formFinalizar.feedback_id = id
            formReset()
            axios
                .post(`${URL_ADMIN}/preadmissao/finalizar-encaminhar`, this.formFinalizar)
                .then((response) => {
                    if (response.status === 201) {
                        this.formFinalizar = _.cloneDeep(this.formFinalizarDefault)
                        this.$refs.janelaFinalizar?.fecharModal()
                        this.mostraSucesso('Finalizado e encaminhado com sucesso!')
                        this.atualizar()
                    }
                    this.preloadFinalizar = false
                })
                .catch(() => {
                    this.preloadFinalizar = false
                })
        },
        listaVagas() {
            this.preload = true
            $.get(`${URL_PUBLICO}/lista-vagas`)
                .done((data) => {
                    this.preload = false
                    this.vagas = data.vagas
                })
                .fail(() => {
                    this.preload = false
                })
        },
        janelaConfirmar(id) {
            this.form.id = id
            this.apagado = false
            this.preload = false
        },
        carregou(dados) {
            this.lista = dados.items
            this.email_padrao = dados.email_padrao
            this.cliente_id = dados.usuario_cliente_id
            this.selecionaTudo = this.tudoMarcado
            this.controle.carregando = false
            this.$nextTick(() => this.syncUrlFiltros())
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            buscarListagem(this, { resetPagina: true })
        },
        previewWhatsappEmail() {
            const apelido = this.authconfiguracao?.apelido || ''
            const observacao = (this.formEmail.observacao || '').trim()
            this.previewWhatsappContexto = {
                nome_destinatario: this.formEmail.candidato_nome || '',
                url_documentos: `${window.location.origin}/${apelido}/documentos`,
                observacao: observacao !== '' ? `${observacao}\n\n` : ''
            }
            this.previewWhatsappTipo = 'admissao_documentos'
            this.previewWhatsappAberto = true
        },
        previewWhatsappFinalizar() {
            const empExame = this.listaEmpresasExames.find(
                (item) => item.id === this.formFinalizar.empresa_exame_id
            )
            const pcmso = this.listaPcmsos.find((item) => item.id === this.formFinalizar.pcmso_id)

            this.previewWhatsappContexto = {
                nome_destinatario: this.dadosFinalizar?.candidato_nome || '',
                tipo_exame: pcmso?.label || 'Admissional',
                clinica_nome: empExame?.nome || '',
                clinica_endereco: empExame?.endereco || '',
                clinica_telefone: empExame?.telefone || '',
                data_encaminhamento: new Date().toLocaleString('pt-BR'),
                data_realizacao: this.formFinalizar.encaminhado_exame_data || ''
            }
            this.previewWhatsappTipo = 'exame_encaminhamento'
            this.previewWhatsappAberto = true
        }
    }
})

registerGlobals(app)
app.mount('#app')
