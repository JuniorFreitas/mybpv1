import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import SolicitacaoDemissao from '../../../components/planejamento/movimentacao/SolicitacaoDemissao'
import SolicitacaoFerias from '../../../components/planejamento/movimentacao/SolicitacaoFerias'
import SolicitacaoAdmissao from '../../../components/planejamento/movimentacao/SolicitacaoAdmissao'
import SolicitacaoValorExtra from '../../../components/planejamento/movimentacao/SolicitacaoValorExtra'
import SolicitacaoMudaCargo from '../../../components/planejamento/movimentacao/SolicitacaoMudaCargo'
import SolicitacaoIntermitenteFixo from '../../../components/planejamento/movimentacao/SolicitacaoIntermitenteFixo'
import SolicitacaoTransferencia from '../../../components/planejamento/movimentacao/SolicitacaoTransferencia'
import { lerVisualizacaoListagem } from '../../../utils/visualizacaoListagem'

const ABAS_META = [
    {
        key: 'demissao',
        label: 'Demissão',
        icon: 'fas fa-user-minus',
        tabId: 'nav-solicitacao-demissao-tab',
        panelId: 'nav-solicitacao-demissao'
    },
    {
        key: 'ferias',
        label: 'Férias',
        icon: 'fas fa-umbrella-beach',
        tabId: 'nav-ferias-tab',
        panelId: 'nav-ferias'
    },
    {
        key: 'admissao',
        label: 'Admissão',
        icon: 'fas fa-user-plus',
        tabId: 'nav-admissao-tab',
        panelId: 'nav-admissao'
    },
    {
        key: 'valorextra',
        label: 'Valor Extra',
        title: 'Liderança de Pessoal e Valor Extra',
        icon: 'fas fa-coins',
        tabId: 'nav-valorextra-tab',
        panelId: 'nav-valorextra'
    },
    {
        key: 'mudacargo',
        label: 'Mudança de Cargo',
        icon: 'fas fa-exchange-alt',
        tabId: 'nav-mudacargo-tab',
        panelId: 'nav-mudacargo'
    },
    {
        key: 'intermitente',
        label: 'Intermitente p/ Fixo',
        title: 'Mudança de Intermitente p/ Fixo',
        icon: 'fas fa-user-tie',
        tabId: 'nav-intermitente-tab',
        panelId: 'nav-intermitente'
    },
    {
        key: 'transferencia',
        label: 'Transferência',
        icon: 'fas fa-random',
        tabId: 'nav-transferencia-tab',
        panelId: 'nav-transferencia'
    }
]

const app = createApp({
    provide() {
        const self = this
        return {
            atualizarUrlMovimentacao(params = {}) {
                self.atualizarUrlMovimentacao(params)
            }
        }
    },
    components: {
        'solicitacao-demissao': SolicitacaoDemissao,
        'solicitacao-ferias': SolicitacaoFerias,
        'solicitacao-admissao': SolicitacaoAdmissao,
        'solicitacao-valor-extra': SolicitacaoValorExtra,
        'solicitacao-muda-cargo': SolicitacaoMudaCargo,
        'solicitacao-intermitente-fixo': SolicitacaoIntermitenteFixo,
        'solicitacao-transferencia': SolicitacaoTransferencia
    },
    data() {
        return {
            preload: false,
            cliente_id: '',

            abas: {
                demissao: false,
                ferias: false,
                admissao: false,
                valorextra: false,
                mudacargo: false,
                intermitente: false,
                transferencia: false
            },
            abasDefault: null,
            permissoes_abas: {},
            aba_ativa: '',
            abasPodeVoltar: false,
            abasPodeAvancar: false,
            visualizacao: lerVisualizacaoListagem('lista')
        }
    },
    computed: {
        abasDisponiveis() {
            return ABAS_META.filter((aba) => !!this.permissoes_abas[aba.key])
        }
    },
    mounted() {
        this.usuarioAutenticado()
        this.abasDefault = _.cloneDeep(this.abas)
        this.listaAbas()
        window.addEventListener('resize', this.atualizarSetasAbas)
    },
    beforeUnmount() {
        window.removeEventListener('resize', this.atualizarSetasAbas)
    },
    methods: {
        urlParamGet() {
            const urlParams = new URLSearchParams(window.location.search)
            return urlParams.get('aba_ativa')
        },
        atualizarUrlMovimentacao(params = {}) {
            const aba = this.aba_ativa
            if (!aba) return
            const next = { aba_ativa: aba, ...params }
            const qs = new URLSearchParams(next).toString()
            const url = window.location.pathname + (qs ? '?' + qs : '')
            window.history.replaceState(null, '', url)
        },
        trocaAba(aba) {
            this.abas = _.cloneDeep(this.abasDefault)
            this.abas[aba] = true
            this.aba_ativa = aba
            this.atualizarUrlMovimentacao({ aba_ativa: aba })
            this.$nextTick(() => {
                this.garantirAbaVisivel(aba)
                this.agendarAtualizacaoSetasAbas()
            })
        },
        rolarAbas(direcao) {
            const el = this.$refs.abasViewport
            if (!el) return
            const passo = Math.max(180, Math.floor(el.clientWidth * 0.7))
            el.scrollBy({ left: direcao * passo, behavior: 'smooth' })
            this.agendarAtualizacaoSetasAbas()
        },
        agendarAtualizacaoSetasAbas() {
            const rodar = () => this.atualizarSetasAbas()
            this.$nextTick(() => {
                if (typeof requestAnimationFrame === 'function') {
                    requestAnimationFrame(() => {
                        rodar()
                        requestAnimationFrame(rodar)
                    })
                } else {
                    rodar()
                }
                ;[50, 150, 300, 500].forEach((ms) => setTimeout(rodar, ms))
            })
        },
        atualizarSetasAbas() {
            const viewport = this.$refs.abasViewport
            if (!viewport || viewport.clientWidth <= 0) {
                this.abasPodeVoltar = false
                this.abasPodeAvancar = false
                return
            }
            const track = viewport.querySelector('.movimentacao-abas__track')
            const conteudo = track ? track.scrollWidth : viewport.scrollWidth
            const max = Math.max(0, conteudo - viewport.clientWidth)
            if (max <= 2) {
                this.abasPodeVoltar = false
                this.abasPodeAvancar = false
                return
            }
            const left = viewport.scrollLeft || 0
            this.abasPodeVoltar = left > 2
            this.abasPodeAvancar = left < max - 2
        },
        garantirAbaVisivel(abaKey) {
            const viewport = this.$refs.abasViewport
            if (!viewport) return
            const meta = this.abasDisponiveis.find((a) => a.key === abaKey)
            if (!meta || !meta.tabId) return
            const btn = viewport.querySelector('#' + meta.tabId)
            if (!btn) return
            const left = btn.offsetLeft
            const right = left + btn.offsetWidth
            const viewLeft = viewport.scrollLeft
            const viewRight = viewLeft + viewport.clientWidth
            if (left < viewLeft) {
                viewport.scrollTo({ left: Math.max(0, left - 12), behavior: 'smooth' })
            } else if (right > viewRight) {
                viewport.scrollTo({ left: right - viewport.clientWidth + 12, behavior: 'smooth' })
            }
        },
        usuarioAutenticado() {
            this.preload = true
            axios
                .get(`${URL_ADMIN}/usuario/autenticado/`)
                .then((response) => {
                    let data = response.data
                    this.cliente_id = data.cliente_id
                })
                .catch(() => {
                    this.preload = false
                })
        },
        listaAbas() {
            this.preload = true
            axios
                .get(`${URL_ADMIN}/planejamento/movimentacao/lista-abas`)
                .then((response) => {
                    let dados = response.data.dados
                    this.preload = false
                    this.permissoes_abas = dados.permissoes_abas || {}
                    const abaUrl = this.urlParamGet()
                    if (abaUrl && this.permissoes_abas[abaUrl]) {
                        this.aba_ativa = abaUrl
                    } else {
                        this.aba_ativa = dados.aba_ativa
                    }
                    this.abas = _.cloneDeep(this.abasDefault)
                    if (this.aba_ativa) {
                        this.abas[this.aba_ativa] = true
                    }
                    this.$nextTick(() => {
                        if (this.aba_ativa) {
                            this.garantirAbaVisivel(this.aba_ativa)
                        }
                        this.agendarAtualizacaoSetasAbas()
                    })
                })
                .catch(() => {
                    this.preload = false
                })
        }
    }
})

registerGlobals(app)
app.mount('#app')
