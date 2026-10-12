import { createApp } from 'vue'
import { registerGlobals } from '../registerGlobals'
import Upload from '../components/Upload.vue'
import MybpCodigoInput from '../components/ui/MybpCodigoInput.vue'

const TAMANHO_CODIGO = 8

const app = createApp({
    components: {
        Upload,
        MybpCodigoInput
    },
    data() {
        return {
            preload: true,
            msgPreload: 'Carregando...',
            enviando: false,
            erro: '',
            aviso: '',
            passo: 'cpf',
            cpf: '',
            codigo: '',
            codigoInvalido: false,
            tamanhoCodigo: TAMANHO_CODIGO,
            emailMascarado: '',
            // Prazos em timestamp (ms); `agora` avança a cada segundo e alimenta os contadores.
            agora: Date.now(),
            codigoExpiraEm: 0,
            reenvioLiberadoEm: 0,
            bloqueadoAte: 0,
            relogio: null,
            dados: {
                autenticado: false,
                status: '',
                public_id: '',
                candidato: null,
                cargo: '',
                empresa: {},
                curriculo_id: null
            },
            apelido: '',
            anexos: [],
            anexosDel: [],
            anexoUploadAndamento: false,
            urlAnexoUpload: `${URL_SITE}/documentos/uploadAnexos`
        }
    },
    computed: {
        urlBase() {
            return `${URL_SITE}/${this.apelido}/carta-oferta/${this.dados.public_id}`
        },
        codigoSegundos() {
            return this.segundosAte(this.codigoExpiraEm)
        },
        reenvioSegundos() {
            return this.segundosAte(this.reenvioLiberadoEm)
        },
        bloqueioSegundos() {
            return this.segundosAte(this.bloqueadoAte)
        },
        bloqueado() {
            return this.bloqueioSegundos > 0
        },
        codigoExpirado() {
            return this.codigoSegundos <= 0
        },
        podeDigitarCodigo() {
            return !this.enviando && !this.bloqueado && !this.codigoExpirado
        },
        podeReenviar() {
            return !this.enviando && !this.bloqueado && this.reenvioSegundos <= 0
        }
    },
    mounted() {
        const pathArray = window.location.pathname.split('/')
        this.apelido = pathArray[1] || ''
        const raw = document.getElementById('dd')
        if (raw) {
            this.dados = Object.assign(this.dados, JSON.parse(raw.value || '{}'))
            raw.remove()
        }
        if (!this.dados.public_id) {
            this.dados.public_id = pathArray[3] || ''
        }
        if (!this.dados.autenticado && this.dados.desafio_pendente) {
            this.emailMascarado = this.dados.email_mascarado || ''
            this.aplicarAcesso(this.dados.acesso)
            this.passo = 'codigo'
        }
        this.relogio = window.setInterval(() => {
            this.agora = Date.now()
        }, 1000)
        this.preload = false
        this.focarCampoAtual()
    },
    beforeUnmount() {
        window.clearInterval(this.relogio)
    },
    methods: {
        segundosAte(prazo) {
            return Math.max(0, Math.ceil((prazo - this.agora) / 1000))
        },
        formatarTempo(segundos) {
            const m = Math.floor(segundos / 60)
            const s = segundos % 60
            return `${String(m).padStart(2, '0')}:${String(s).padStart(2, '0')}`
        },
        aplicarAcesso(acesso) {
            if (!acesso) {
                return
            }
            this.agora = Date.now()
            this.codigoExpiraEm = this.agora + (acesso.codigo_segundos || 0) * 1000
            this.reenvioLiberadoEm = this.agora + (acesso.reenvio_segundos || 0) * 1000
            this.bloqueadoAte = this.agora + (acesso.bloqueio_segundos || 0) * 1000
        },
        focarCampoAtual() {
            this.$nextTick(() => {
                if (this.dados.autenticado) {
                    return
                }
                if (this.passo === 'codigo') {
                    this.$refs.codigoInput?.focar(0)
                } else {
                    this.$refs.cpfInput?.focus()
                }
            })
        },
        // Reaproveita a validação padrão do projeto (funcoes.js), que marca o campo e exibe a mensagem.
        validarCpf(obrigatorio = false) {
            const campo = this.$refs.cpfInput
            if (!campo) {
                return true
            }
            return obrigatorio ? valida_cpf_vazio(campo) : valida_cpf(campo)
        },
        alterarCpf() {
            this.erro = ''
            this.aviso = ''
            this.passo = 'cpf'
            this.focarCampoAtual()
        },
        // Sem `reenvio`, valida o CPF digitado; com `reenvio`, usa o CPF já confirmado na sessão.
        async solicitarCodigo(reenvio = false) {
            this.erro = ''
            this.aviso = ''
            if (reenvio && !this.podeReenviar) {
                return
            }
            if (!reenvio && !this.validarCpf(true)) {
                return
            }
            this.enviando = true
            try {
                const { data } = await axios.post(`${this.urlBase}/solicitar-codigo`, reenvio ? {} : { cpf: this.cpf })
                this.emailMascarado = data.email_mascarado || ''
                this.aplicarAcesso(data.acesso)
                this.codigo = ''
                this.codigoInvalido = false
                this.passo = 'codigo'
                if (reenvio) {
                    this.aviso = 'Novo código enviado.'
                }
            } catch (err) {
                this.tratarErro(err, 'Não foi possível enviar o código.')
            } finally {
                this.enviando = false
                this.focarCampoAtual()
            }
        },
        async validarCodigo() {
            if (this.enviando || !this.podeDigitarCodigo) {
                return
            }
            this.erro = ''
            this.aviso = ''
            if (String(this.codigo).length < this.tamanhoCodigo) {
                this.erro = `Informe o código de ${this.tamanhoCodigo} caracteres.`
                return
            }
            this.enviando = true
            try {
                const { data } = await axios.post(`${this.urlBase}/validar-codigo`, { codigo: this.codigo })
                this.dados = Object.assign(this.dados, data, { autenticado: true })
            } catch (err) {
                this.tratarErro(err, 'Código inválido ou expirado.')
                this.codigo = ''
                this.codigoInvalido = true
            } finally {
                this.enviando = false
                this.focarCampoAtual()
            }
        },
        tratarErro(err, padrao) {
            const resposta = err?.response
            this.aplicarAcesso(resposta?.data?.acesso)
            this.erro =
                resposta?.status === 429
                    ? 'Muitas requisições. Aguarde um minuto e tente novamente.'
                    : resposta?.data?.message || padrao
        },
        salvar() {
            this.preload = true
            this.msgPreload = 'Salvando...'
            axios
                .post(`${this.urlBase}/salvar`, { arquivo: this.anexos[0] })
                .then(() => {
                    window.location.reload(true)
                })
                .catch((error) => {
                    this.preload = false
                    this.erro = error?.response?.data?.message || 'Não foi possível enviar a carta.'
                })
        }
    },
    watch: {
        codigo(valor) {
            if (valor) {
                this.codigoInvalido = false
            }
        }
    }
})

registerGlobals(app)
app.mount('#app')
