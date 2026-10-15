import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import endereco from '../../../components/Endereco'
import telefone from '../../../components/Telefones'
import datepicker from '../../../components/DatePicker'
import classificar from '../../../components/Classificar'
import DateRangeFilter from '../../../components/DateRangeFilter.vue'
import ComboboxAutoComplete from '../../../components/ComboboxAutoComplete.vue'
import FiltroListagem from '../../../components/ui/FiltroListagem.vue'
import MybpCardCampo from '../../../components/ui/MybpCardCampo.vue'
import MybpStatusBadge from '../../../components/ui/MybpStatusBadge.vue'

const UFS = [
    'AC', 'AL', 'AP', 'AM', 'BA', 'CE', 'DF', 'ES', 'GO', 'MA',
    'MT', 'MS', 'MG', 'PA', 'PB', 'PR', 'PE', 'PI', 'RJ', 'RN',
    'RS', 'RO', 'RR', 'SC', 'SP', 'SE', 'TO'
]

const app = createApp({
    components: {
        endereco,
        datepicker,
        telefone,
        classificar,
        DateRangeFilter,
        ComboboxAutoComplete,
        FiltroListagem,
        MybpCardCampo,
        MybpStatusBadge
    },
    data() {
        return {
            tituloJanela: 'Visualizando Curriculo',
            preloadAjax: false,
            editando: false,
            apagado: false,
            opened: [],
            cliente_id: 0,
            dropdownAbertoId: null,
            filtrosAvancadosAbertos: false,
            urlCartaOferta: `${URL_ADMIN}/admissao/documentos/carta-oferta`,
            urlsWorkflow: {},
            etapasFiltroApi: [],
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
                curriculo: {},
                etapa_status: [],
                simulados: [],
                carta_resumo: null,
                url_carta_oferta: ''
            },
            formDefault: null,

            cadastrado: false,
            atualizado: false,
            lista: [],
            ufs: [],
            vagas: [],

            controle: {
                carregando: false,
                dados: {
                    caminho_autocomplete: 'autocomplete/todas-vagas-ativas',
                    autocomplete_label_anterior: '',
                    autocomplete_label: '',
                    pages: 20,
                    campoBusca: '',
                    campoVaga: '',
                    campoUf: '',
                    campoPcd: '',
                    campoCPF: '',
                    campoProvas: '',
                    campoNota: '',
                    campoStatus: '',
                    campoEtapa: '',
                    campoPendenciaWorkflow: '',
                    campoCliente: '',
                    campoCartaOferta: '',
                    campoDisponibilidadeSabado: '',
                    campoDisponibilidadeDomingo: '',
                    filtroPeriodo: false,
                    dataInicio: '',
                    dataFim: '',
                    periodo: ''
                }
            }
        }
    },
    computed: {
        totalFiltrosAtivos() {
            return this.totalFiltrosAtivosPrimarios + this.totalFiltrosAtivosAvancados
        },
        totalFiltrosAtivosPrimarios() {
            const d = this.controle.dados
            let total = [d.campoBusca, d.campoCPF, d.campoVaga, d.campoUf, d.campoStatus, d.campoPcd].filter(
                (v) => v !== '' && v !== null && v !== undefined
            ).length
            if (d.filtroPeriodo && d.dataInicio && d.dataFim) total++
            if (Number(d.pages) !== 20) total++
            return total
        },
        totalFiltrosAtivosAvancados() {
            const d = this.controle.dados
            return [
                d.campoProvas,
                d.campoNota,
                d.campoEtapa,
                d.campoPendenciaWorkflow,
                d.campoCartaOferta,
                d.campoDisponibilidadeSabado,
                d.campoDisponibilidadeDomingo
            ].filter((v) => v !== '' && v !== null && v !== undefined).length
        },
        campoBuscaUnificada() {
            const d = this.controle.dados
            if (d.campoCPF) return d.campoCPF
            return d.campoBusca || ''
        },
        buscaUnificadaEhCpf() {
            return !!this.controle.dados.campoCPF
        },
        campoPagesCombo: {
            get() {
                return this.controle.dados.pages === '' || this.controle.dados.pages == null
                    ? 20
                    : Number(this.controle.dados.pages)
            },
            set(v) {
                this.controle.dados.pages = v === '' || v == null ? 20 : Number(v)
            }
        },
        filtroUfOpcoes() {
            return [{ value: '', label: 'Todos os estados' }, ...UFS.map((uf) => ({ value: uf, label: uf }))]
        },
        filtroSimNaoOpcoes() {
            return [
                { value: '', label: 'Geral' },
                { value: 'true', label: 'Sim' },
                { value: 'false', label: 'Não' }
            ]
        },
        filtroPagesOpcoes() {
            return [20, 50, 100].map((n) => ({ value: n, label: String(n) }))
        },
        filtroStatusOpcoes() {
            return [
                { value: '', label: 'Sem filtro' },
                { value: 'classificado', label: 'Classificado' },
                { value: 'desclassificado', label: 'Desclassificado' },
                { value: 'sem_status', label: 'Sem status' }
            ]
        },
        filtroProvaOpcoes() {
            return [
                { value: '', label: 'Sem filtro' },
                { value: 'sim', label: 'Finalizada' },
                { value: 'nao', label: 'Não realizada' },
                { value: 'andamento', label: 'Não finalizada / andamento' }
            ]
        },
        filtroNotaOpcoes() {
            return [
                { value: '', label: 'Sem filtro' },
                { value: '0', label: '0' },
                { value: '1-5', label: '1 à 5' },
                { value: '5-7', label: '5 à 7' },
                { value: '8-10', label: '8 à 10' }
            ]
        },
        filtroEtapaOpcoes() {
            const base = [{ value: '', label: 'Sem filtro' }]
            if (this.etapasFiltroApi.length) {
                return base.concat(
                    this.etapasFiltroApi.map((e) => ({
                        value: e.nome,
                        label: e.nome
                    }))
                )
            }
            return base.concat([
                { value: 'Prova presencial', label: 'Classificado Prova presencial' },
                { value: 'Teste de digitação', label: 'Classificado Teste de digitação' },
                { value: 'Dinâmica de grupo', label: 'Classificado Dinâmica de grupo' },
                { value: 'Entrevista Individual', label: 'Classificado Entrevista Individual' },
                { value: 'Entrevista RH', label: 'Classificado Entrevista RH' },
                { value: 'Entrevista Gestor', label: 'Classificado Entrevista Gestor' },
                { value: 'Apto para Admissao', label: 'Apto para Admissao' },
                { value: 'Aviso Recesso', label: 'Aviso Recesso' }
            ])
        },
        filtroPendenciaWorkflowOpcoes() {
            const base = [{ value: '', label: 'Sem filtro' }]
            const sistema = (this.etapasFiltroApi || []).filter(
                (e) => e.tipo === 'sistema' || e.is_apto_admissao || e.chave === 'apto_admissao'
            )
            if (!sistema.length) {
                return base.concat([
                    { value: 'apto_admissao', label: 'Pendente: Apto admissão' },
                    { value: 'carta_oferta', label: 'Pendente: Carta oferta' },
                    { value: 'preadmissao_documentos', label: 'Pendente: Docs pré-admissão' },
                    { value: 'entrevista_tecnica', label: 'Pendente: Entrevista técnica' },
                    { value: 'parecer_rota', label: 'Pendente: Parecer rota' }
                ])
            }
            return base.concat(
                sistema.map((e) => ({
                    value: e.chave,
                    label: `Pendente: ${e.nome}`
                }))
            )
        },
        filtroCartaOfertaOpcoes() {
            return [
                { value: '', label: 'Sem filtro' },
                { value: 'Aguardando', label: 'Aguardando' },
                { value: 'Sim', label: 'Aceita' },
                { value: 'Não', label: 'Recusada' }
            ]
        },
        formacaoTipo() {
            return this.form?.curriculo?.formacao?.tipo || this.form?.curriculo?.formacao || ''
        },
        vagaLabelModal() {
            const va = this.form?.vaga_aberta
            if (!va?.vaga_selecionada) return 'Não informado'
            const mun = va.municipio
            return `${va.vaga_selecionada.nome}${mun ? ` - ${mun.nome} - ${mun.uf}` : ''}`
        }
    },
    mounted() {
        this.formDefault = _.cloneDeep(this.form)
        this.formClassificarDefault = _.cloneDeep(this.formClassificar)
        this.formDesclassificarDefault = _.cloneDeep(this.formDesclassificar)
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
        selecionaVaga(obj) {
            this.controle.dados.campoVaga = obj.id
            this.controle.dados.autocomplete_label = obj.label
            this.controle.dados.autocomplete_label_anterior = obj.label
            this.controle.carregando = true
            setTimeout(() => {
                this.atualizar()
            }, 400)
        },
        resetaCampo() {
            if (this.controle.dados.autocomplete_label_anterior !== this.controle.dados.autocomplete_label) {
                this.controle.dados.autocomplete_label_anterior = ''
                this.controle.dados.autocomplete_label = ''
                this.controle.dados.campoVaga = ''
                this.atualizar()
            }
        },
        enviarNotificacao(fone, nome, curriculo_id, vaga_id, etapa_id, mensagem = null) {
            this.preloadAjax = true
            const dados = { fone, nome, curriculo_id, vaga_id, etapa_id, mensagem }
            $.post(`${URL_ADMIN}/enviaNotificacao`, dados)
                .done(() => {
                    this.$refs.janelaWhatsApp?.fecharModal()
                    mostraSucesso('', `Notificação para ${nome} enviada com sucesso!`)
                    this.preloadAjax = false
                    this.atualizar()
                })
                .fail(() => {
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
                    .then(() => {
                        this.atualizar()
                    })
            }
            setTimeout(() => {
                this.atualizar()
            }, 100)
            this.formAlterar(resposta.id)
        },
        formAlterar(id) {
            this.form.id = id
            this.cadastrado = false
            this.atualizado = false
            this.editando = false
            this.preloadAjax = true
            Object.assign(this.formClassificar, this.formClassificarDefault)
            Object.assign(this.formDesclassificar, this.formDesclassificarDefault)
            formReset()

            axios
                .get(`${URL_ADMIN}/curriculos/curriculos-selecionados/${id}/selecionado`)
                .then((response) => {
                    const data = response.data
                    Object.assign(this.form, data)
                    if (data.url_carta_oferta) {
                        this.urlCartaOferta = data.url_carta_oferta
                    }
                    this.tituloJanela = `Visualizando Curriculo - ${data.curriculo?.nome || ''}`
                    this.editando = true
                    this.preloadAjax = false
                    setupCampo()
                })
                .catch(() => {
                    this.preloadAjax = false
                })
        },
        abrirVisualizar(id) {
            this.fecharDropdown()
            this.formAlterar(id)
            this.$refs.janelaCadastrar?.abrirModal()
        },
        abrirWhatsApp(item) {
            this.fecharDropdown()
            this.formAlterar(item.id)
            this.$refs.janelaWhatsApp?.abrirModal()
        },
        urlPdfCurriculo(item) {
            if (!item?.curriculo_ctoken) return '#'
            return `${URL_ADMIN}/curriculos/recrutamentos/${item.curriculo_ctoken}`
        },
        urlGerenciarCarta() {
            return this.form?.url_carta_oferta || this.urlCartaOferta
        },
        carregou(dados) {
            this.lista = dados.itens || []
            this.cliente_id = dados.usuario_cliente_id
            if (dados.url_carta_oferta) {
                this.urlCartaOferta = dados.url_carta_oferta
            }
            if (dados.urls_workflow) {
                this.urlsWorkflow = dados.urls_workflow
            }
            if (Array.isArray(dados.etapas_filtro)) {
                this.etapasFiltroApi = dados.etapas_filtro
            }
            this.controle.carregando = false
        },
        carregando() {
            this.controle.carregando = true
        },
        atualizar() {
            this.sincronizarPeriodoBr()
            if (this.$refs.componente) {
                this.$refs.componente.atual = 1
                if (typeof this.$refs.componente.buscar === 'function') {
                    this.$refs.componente.buscar()
                }
            }
        },
        sincronizarPeriodoBr() {
            const d = this.controle.dados
            if (d.filtroPeriodo && d.dataInicio && d.dataFim) {
                d.periodo = `${this.isoParaBr(d.dataInicio)} até ${this.isoParaBr(d.dataFim)}`
            } else {
                d.periodo = ''
            }
        },
        isoParaBr(iso) {
            if (!iso || !/^\d{4}-\d{2}-\d{2}$/.test(iso)) return ''
            const [y, m, day] = iso.split('-')
            return `${day}/${m}/${y}`
        },
        formatarCpfDigitos(valor) {
            const d = String(valor || '').replace(/\D/g, '').slice(0, 11)
            if (d.length <= 3) return d
            if (d.length <= 6) return `${d.slice(0, 3)}.${d.slice(3)}`
            if (d.length <= 9) return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6)}`
            return `${d.slice(0, 3)}.${d.slice(3, 6)}.${d.slice(6, 9)}-${d.slice(9)}`
        },
        parecePadraoCpf(valor) {
            const raw = String(valor || '').trim()
            if (!raw) return false
            if (!/^[\d.\-\s]+$/.test(raw)) return false
            const digitos = raw.replace(/\D/g, '')
            return digitos.length > 0 && digitos.length <= 11
        },
        onInputBuscaUnificada(event) {
            const valor = event && event.target ? event.target.value : ''
            const d = this.controle.dados
            if (!valor) {
                d.campoBusca = ''
                d.campoCPF = ''
                return
            }
            if (this.parecePadraoCpf(valor)) {
                const mascarado = this.formatarCpfDigitos(valor)
                d.campoCPF = mascarado
                d.campoBusca = ''
                if (event.target && event.target.value !== mascarado) {
                    event.target.value = mascarado
                }
                return
            }
            d.campoBusca = valor
            d.campoCPF = ''
        },
        onPeriodoChange() {
            this.atualizar()
        },
        onSelectFiltro() {
            this.atualizar()
        },
        onSelectProva() {
            this.filtraProva()
        },
        fecharOutrosComboboxes(excetoId) {
            const mapa = {
                'sel-filtro-uf': 'comboFiltroUf',
                'sel-filtro-status': 'comboFiltroStatus',
                'sel-filtro-pcd': 'comboFiltroPcd',
                'sel-filtro-pages': 'comboFiltroPages',
                'sel-filtro-prova': 'comboFiltroProva',
                'sel-filtro-nota': 'comboFiltroNota',
                'sel-filtro-etapa': 'comboFiltroEtapa',
                'sel-filtro-pendencia': 'comboFiltroPendencia',
                'sel-filtro-carta': 'comboFiltroCarta',
                'sel-filtro-sabado': 'comboFiltroSabado',
                'sel-filtro-domingo': 'comboFiltroDomingo'
            }
            Object.keys(mapa).forEach((id) => {
                if (id === excetoId) return
                const ref = this.$refs[mapa[id]]
                if (ref && typeof ref.fechar === 'function') ref.fechar()
                else if (ref && typeof ref.close === 'function') ref.close()
            })
        },
        limparFiltros() {
            const pages = this.controle.dados.pages || 20
            this.controle.dados = {
                ...this.controle.dados,
                autocomplete_label_anterior: '',
                autocomplete_label: '',
                campoBusca: '',
                campoCPF: '',
                campoVaga: '',
                campoUf: '',
                campoPcd: '',
                campoProvas: '',
                campoNota: '',
                campoStatus: '',
                campoEtapa: '',
                campoPendenciaWorkflow: '',
                campoCliente: '',
                campoCartaOferta: '',
                campoDisponibilidadeSabado: '',
                campoDisponibilidadeDomingo: '',
                filtroPeriodo: false,
                dataInicio: '',
                dataFim: '',
                periodo: '',
                pages
            }
            this.filtrosAvancadosAbertos = false
            this.atualizar()
        },
        chaveStatusSel(item) {
            const etapa = item?.etapa_status?.[0]
            const status = etapa?.status || item?.status || ''
            if (status === 'desclassificado') return 'reprovado'
            if (status === 'admitido' || status === 'aprovado') return 'aprovado'
            if (item?.selecionado === 'standby') return 'gestor'
            if (status === 'aguardando') return 'pendente'
            if (status === 'classificado') return 'neutro'
            return 'pendente'
        },
        textoStatusSel(item) {
            const etapa = item?.etapa_status?.[0]
            const sel = item?.selecionado === 'standby' ? 'Stand by' : null
            if (etapa?.etapa && etapa?.status) {
                return sel ? `${sel} · ${etapa.etapa}` : `${etapa.etapa} · ${etapa.status}`
            }
            if (sel) return sel
            if (etapa?.status) return etapa.status
            if (item?.status) return item.status
            return 'Sem status'
        },
        textoEtapaSel(item) {
            return item?.etapa_status?.[0]?.etapa || 'Não informado'
        },
        textoEtapaStatusCard(item) {
            const etapa = item?.etapa_status?.[0]
            if (etapa?.etapa && etapa?.status) return `${etapa.etapa} · ${etapa.status}`
            if (etapa?.etapa) return etapa.etapa
            if (item?.status) return item.status
            return 'Aguardando classificação'
        },
        textoSelecionadoCard(item) {
            const sel = item?.selecionado
            if (sel === 'sim') return 'Sim'
            if (sel === 'standby') return 'Stand by'
            if (sel === 'nao') return 'Não'
            return sel || 'Não informado'
        },
        textoContatoCard(item) {
            return item?.tel_principal?.numero || item?.curriculo?.tel_principal?.numero || 'Não informado'
        },
        textoCartaSel(item) {
            return item?.carta_resumo?.texto || 'Não enviada'
        },
        tomCartaSel(item) {
            const chave = item?.carta_resumo?.chave
            if (chave === 'aceita') return 'positivo'
            if (chave === 'recusada' || chave === 'expirada') return 'negativo'
            if (chave === 'aguardando') return 'meta'
            return 'meta'
        },
        textoSimNao(valor) {
            if (valor === true || valor === 'true' || valor === 1) return 'Sim'
            if (valor === false || valor === 'false' || valor === 0) return 'Não'
            return 'Não informado'
        },
        tomSimNao(valor) {
            if (valor === true || valor === 'true' || valor === 1) return 'positivo'
            if (valor === false || valor === 'false' || valor === 0) return 'negativo'
            return 'meta'
        },
        labelVagaCard(item) {
            const va = item?.vaga_aberta
            const nome =
                va?.vaga_selecionada?.nome ||
                va?.vaga?.nome ||
                item?.vaga_selecionada?.nome ||
                null
            if (!nome) return 'Não informado'
            const mun = va?.municipio
            return mun ? `${nome} - ${mun.nome} - ${mun.uf}` : nome
        },
        // Paridade SGI: classificar se status classificado OU selecionado sim/standby
        podeClassificar(item) {
            if (!item) return false
            if (item.status === 'desclassificado') return false
            if (item.status === 'classificado') return true
            return item.selecionado === 'sim' || item.selecionado === 'standby'
        },
        podeWhatsApp(item) {
            const tel = item?.tel_principal || item?.curriculo?.tel_principal
            if (!tel || tel.tipo !== 'whatsapp') return false

            const progresso = item?.workflow_progresso?.etapas || []
            const apto = progresso.find(
                (e) => e.is_apto_admissao || e.chave === 'apto_admissao'
            )
            if (apto) {
                return !!apto.concluida
            }

            const etapa = item?.etapa_status?.[0]
            return !!(etapa && (etapa.etapa === 'Apto para Admissao' || etapa.etapa === 'Apto para Admissão'))
        },
        pendenciasObrigatorias(item) {
            return item?.workflow_progresso?.pendencias_obrigatorias || []
        },
        ctasSistemaPendentes(item) {
            const etapas = item?.workflow_progresso?.etapas || []
            const chavesSistema = [
                'carta_oferta',
                'preadmissao_documentos',
                'entrevista_tecnica',
                'parecer_rota'
            ]
            return etapas.filter(
                (e) =>
                    chavesSistema.includes(e.chave) &&
                    !e.concluida &&
                    e.liberada &&
                    this.urlsWorkflow[e.chave]
            )
        },
        urlModuloWorkflow(chave) {
            return this.urlsWorkflow[chave] || '#'
        },
        prontoParaAdmissao(item) {
            return !!item?.workflow_progresso?.pronto_para_admissao
        },
        headerEtapaClass(etapa) {
            if (!etapa) return ''
            if (etapa.status === 'classificado') return 'bg-primary text-white'
            if (etapa.status === 'desclassificado') return 'bg-danger text-white'
            if (etapa.status === 'admitido') return 'bg-success'
            return ''
        },
        toggleDropdown(itemId) {
            this.dropdownAbertoId = this.dropdownAbertoId === itemId ? null : itemId
        },
        isDropdownOpen(itemId) {
            return this.dropdownAbertoId === itemId
        },
        fecharDropdown() {
            this.dropdownAbertoId = null
        }
    }
})
registerGlobals(app)
app.mount('#app')
