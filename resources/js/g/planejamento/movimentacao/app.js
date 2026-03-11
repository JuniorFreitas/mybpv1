import { createApp } from 'vue'
import { registerGlobals } from '../../../registerGlobals'
import SolicitacaoDemissao from '../../../components/planejamento/movimentacao/SolicitacaoDemissao'
import SolicitacaoFerias from '../../../components/planejamento/movimentacao/SolicitacaoFerias'
import SolicitacaoAdmissao from '../../../components/planejamento/movimentacao/SolicitacaoAdmissao'
import SolicitacaoValorExtra from '../../../components/planejamento/movimentacao/SolicitacaoValorExtra'
import SolicitacaoMudaCargo from '../../../components/planejamento/movimentacao/SolicitacaoMudaCargo'
import SolicitacaoIntermitenteFixo from '../../../components/planejamento/movimentacao/SolicitacaoIntermitenteFixo'
import SolicitacaoTransferencia from '../../../components/planejamento/movimentacao/SolicitacaoTransferencia'

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
            permissoes_abas: [],
            aba_ativa: ''
        }
    },
    mounted() {
        this.usuarioAutenticado()
        this.abasDefault = _.cloneDeep(this.abas) //copia
        this.listaAbas()
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
            this.abas = _.cloneDeep(this.abasDefault) //copia
            this.abas[aba] = true
            this.aba_ativa = aba
            this.atualizarUrlMovimentacao({ aba_ativa: aba })
        },
        usuarioAutenticado() {
            this.preload = true
            axios
                .get(`${URL_ADMIN}/usuario/autenticado/`)
                .then((response) => {
                    let data = response.data
                    this.cliente_id = data.cliente_id
                })
                .catch((error) => {
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
                    this.permissoes_abas = dados.permissoes_abas
                    const abaUrl = this.urlParamGet()
                    if (abaUrl && this.permissoes_abas[abaUrl]) {
                        this.aba_ativa = abaUrl
                    } else {
                        this.aba_ativa = dados.aba_ativa
                    }
                    this.abas = _.cloneDeep(this.abasDefault)
                    this.abas[this.aba_ativa] = true
                })
                .catch((data) => {
                    this.preload = false
                })
        }
    }
})

registerGlobals(app)
app.mount('#app')
