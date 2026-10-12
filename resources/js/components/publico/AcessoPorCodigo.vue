<template>
    <section class="co-card">
        <div class="co-passos" aria-hidden="true">
            <span class="ativo"></span>
            <span :class="{ ativo: passo === 'codigo' }"></span>
        </div>

        <form v-if="passo === 'cpf'" @submit.prevent="solicitarCodigo(false)" novalidate>
            <div class="co-card__icone"><i class="fa fa-lock"></i></div>
            <h1 class="co-card__titulo">{{ titulo }}</h1>
            <p class="co-card__texto">
                Por segurança, informe seu CPF. Enviaremos um código de {{ tamanhoCodigo }} caracteres
                para o e-mail cadastrado.
            </p>

            <label class="co-label" for="co-cpf">CPF</label>
            <input
                id="co-cpf"
                ref="cpfInput"
                type="text"
                class="co-input"
                v-model="cpf"
                v-mascara:cpf
                maxlength="14"
                inputmode="numeric"
                autocomplete="off"
                placeholder="000.000.000-00"
                :disabled="enviando"
                @blur="validarCpf(false)"
            >
            <button type="submit" class="co-btn" :disabled="enviando">
                <i class="fa" :class="enviando ? 'fa-spinner fa-spin' : 'fa-paper-plane'"></i>
                Enviar código
            </button>
        </form>

        <form v-else @submit.prevent="validarCodigo" novalidate>
            <div class="co-card__icone"><i class="fa fa-envelope-open"></i></div>
            <h1 class="co-card__titulo">Digite o código</h1>
            <p class="co-card__texto">
                Enviamos um código de {{ tamanhoCodigo }} caracteres
                <template v-if="emailMascarado">para <strong>{{ emailMascarado }}</strong></template
                ><template v-else>para o e-mail cadastrado</template>. Ele só pode ser usado uma vez.
            </p>

            <mybp-codigo-input
                ref="codigoInput"
                v-model="codigo"
                :tamanho="tamanhoCodigo"
                :disabled="!podeDigitarCodigo"
                :invalido="codigoInvalido"
                rotulo="Código de acesso"
                @completo="validarCodigo"
            ></mybp-codigo-input>

            <p v-if="bloqueado" class="co-timer co-timer--alerta" role="status">
                <i class="fa fa-ban"></i>
                Bloqueado por tentativas. Tente novamente em <strong>{{ formatarTempo(bloqueioSegundos) }}</strong>
            </p>
            <p v-else-if="codigoExpirado" class="co-timer co-timer--alerta" role="status">
                <i class="fa fa-clock"></i>
                O código expirou. Reenvie para receber um novo.
            </p>
            <p v-else class="co-timer" :class="{ 'co-timer--alerta': codigoSegundos <= 60 }">
                <i class="fa fa-clock"></i>
                Código válido por <strong>{{ formatarTempo(codigoSegundos) }}</strong>
            </p>

            <button type="submit" class="co-btn" :disabled="!podeDigitarCodigo || codigo.length < tamanhoCodigo">
                <i class="fa" :class="enviando ? 'fa-spinner fa-spin' : 'fa-check'"></i>
                Validar e continuar
            </button>

            <div class="co-links">
                <button type="button" class="co-link" :disabled="!podeReenviar" @click="solicitarCodigo(true)">
                    Reenviar código<template v-if="reenvioSegundos > 0 && !bloqueado"> ({{ reenvioSegundos }}s)</template>
                </button>
                <button type="button" class="co-link" :disabled="enviando" @click="alterarCpf">
                    Alterar CPF
                </button>
            </div>
        </form>

        <div v-if="aviso" class="co-alert co-alert--ok" role="status">{{ aviso }}</div>
        <div v-if="erro" class="co-alert co-alert--erro" role="alert">{{ erro }}</div>
    </section>
</template>

<script>
import MybpCodigoInput from '../ui/MybpCodigoInput.vue'

const TAMANHO_CODIGO = 8

/**
 * Acesso de telas públicas: CPF -> código de uso único por e-mail (validade, reenvio e bloqueio
 * controlados pelo backend — ver CodigoAcessoService).
 *
 * Endpoints esperados em `urlBase`: POST `/solicitar-codigo` ({ cpf } ou vazio para reenviar)
 * e POST `/validar-codigo` ({ codigo }). `extras` vai junto no corpo das duas chamadas.
 * Estilos: resources/views/layouts/publico/estilos.blade.php.
 */
export default {
    name: 'AcessoPorCodigo',
    components: { MybpCodigoInput },
    props: {
        urlBase: { type: String, required: true },
        titulo: { type: String, default: 'Acesso' },
        extras: { type: Object, default: () => ({}) },
        // Estado vindo do servidor quando o CPF já foi confirmado nesta sessão:
        // { desafio_pendente, email_mascarado, acesso }
        inicial: { type: Object, default: () => ({}) }
    },
    emits: ['autenticado'],
    data() {
        return {
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
            relogio: null
        }
    },
    computed: {
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
    watch: {
        codigo(valor) {
            if (valor) {
                this.codigoInvalido = false
            }
        }
    },
    mounted() {
        if (this.inicial?.desafio_pendente) {
            this.emailMascarado = this.inicial.email_mascarado || ''
            this.aplicarAcesso(this.inicial.acesso)
            this.passo = 'codigo'
        }
        this.relogio = window.setInterval(() => {
            this.agora = Date.now()
        }, 1000)
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
            return obrigatorio ? window.valida_cpf_vazio(campo) : window.valida_cpf(campo)
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
                const corpo = reenvio ? { ...this.extras } : { ...this.extras, cpf: this.cpf }
                const { data } = await axios.post(`${this.urlBase}/solicitar-codigo`, corpo)
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
                const { data } = await axios.post(`${this.urlBase}/validar-codigo`, { ...this.extras, codigo: this.codigo })
                this.$emit('autenticado', data)
            } catch (err) {
                this.tratarErro(err, 'Código inválido ou expirado.')
                this.codigo = ''
                this.codigoInvalido = true
                this.focarCampoAtual()
            } finally {
                this.enviando = false
            }
        },
        tratarErro(err, padrao) {
            const resposta = err?.response
            this.aplicarAcesso(resposta?.data?.acesso)
            this.erro =
                resposta?.status === 429
                    ? 'Muitas requisições. Aguarde um minuto e tente novamente.'
                    : resposta?.data?.message || padrao
        }
    }
}
</script>
