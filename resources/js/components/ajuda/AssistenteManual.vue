<template>
    <div class="mybp-assistente" :class="{ 'mybp-assistente--aberto': aberto }">
        <transition name="mybp-assistente-painel">
            <section
                v-if="aberto"
                class="mybp-assistente-painel"
                role="dialog"
                aria-label="Assistente MyBPIN"
                @keydown.esc="fechar"
            >
                <header class="mybp-assistente-cabecalho">
                    <div class="mybp-assistente-cabecalho-icone" aria-hidden="true"><i class="bx bx-bot"></i></div>
                    <div class="mybp-assistente-cabecalho-titulos">
                        <strong>Assistente MyBPIN</strong>
                        <span>Tire suas dúvidas sobre o sistema</span>
                    </div>
                    <button
                        v-if="mensagens.length"
                        type="button"
                        class="mybp-assistente-acao"
                        title="Nova conversa"
                        aria-label="Nova conversa"
                        :disabled="enviando"
                        @click="novaConversa"
                    >
                        <i class="bx bx-trash"></i>
                    </button>
                    <button type="button" class="mybp-assistente-acao" title="Fechar" aria-label="Fechar" @click="fechar">
                        <i class="bx bx-x"></i>
                    </button>
                </header>

                <div ref="lista" class="mybp-assistente-mensagens" aria-live="polite" data-clarity-mask="true">
                    <div v-if="!mensagens.length" class="mybp-assistente-boas-vindas">
                        <p>
                            Olá! Eu respondo dúvidas de uso do <strong>MyBPIN</strong>: onde fica cada tela,
                            para que serve e como fazer.
                        </p>
                        <span class="mybp-assistente-sugestoes-titulo">Experimente perguntar</span>
                        <button
                            v-for="sugestao in SUGESTOES"
                            :key="sugestao"
                            type="button"
                            class="mybp-assistente-sugestao"
                            @click="enviar(sugestao)"
                        >
                            {{ sugestao }}
                        </button>
                    </div>

                    <assistente-manual-mensagem
                        v-for="mensagem in mensagens"
                        :key="mensagem.id"
                        :mensagem="mensagem"
                        @abrir-fonte="abrirFonte"
                    />

                    <div v-if="enviando" class="mybp-assistente-msg mybp-assistente-msg--assistente">
                        <div class="mybp-assistente-avatar" aria-hidden="true"><i class="bx bx-bot"></i></div>
                        <div class="mybp-assistente-balao mybp-assistente-digitando" aria-label="Preparando a resposta">
                            <span></span><span></span><span></span>
                        </div>
                    </div>
                </div>

                <form class="mybp-assistente-form" @submit.prevent="enviar()">
                    <textarea
                        ref="campo"
                        v-model="texto"
                        class="mybp-assistente-campo"
                        rows="1"
                        :maxlength="MAX_CARACTERES"
                        placeholder="Digite sua dúvida sobre o sistema"
                        aria-label="Sua dúvida"
                        @keydown.enter.exact.prevent="enviar()"
                        @input="ajustarAltura"
                    ></textarea>
                    <button
                        type="submit"
                        class="mybp-assistente-enviar"
                        :disabled="!podeEnviar"
                        title="Enviar"
                        aria-label="Enviar"
                    >
                        <i class="bx bx-send"></i>
                    </button>
                </form>

                <footer class="mybp-assistente-rodape">
                    Respostas geradas por IA. Não informe dados pessoais nem senhas.
                </footer>
            </section>
        </transition>

        <button
            type="button"
            class="mybp-assistente-botao"
            :aria-expanded="aberto ? 'true' : 'false'"
            :title="aberto ? 'Fechar assistente' : 'Dúvidas? Pergunte ao assistente'"
            :aria-label="aberto ? 'Fechar assistente' : 'Abrir assistente'"
            @click="alternar"
        >
            <i class="bx" :class="aberto ? 'bx-x' : 'bx-bot'"></i>
        </button>

        <!-- No body: dentro do widget (position fixed) a modal ficaria atrás do backdrop do Bootstrap. -->
        <teleport to="body">
            <modal-component
                id="modalAssistenteManualSecao"
                ref="modalSecao"
                :titulo="fonteAberta ? fonteAberta.titulo : 'Detalhes'"
                :size="90"
                @fechou="fonteAberta = null"
            >
                <template #conteudo>
                    <span v-if="fonteAberta && fonteAberta.caminho_menu" class="mybp-assistente-secao-caminho">
                        Menu › {{ fonteAberta.caminho_menu }}
                    </span>
                    <iframe
                        v-if="fonteAberta"
                        :src="urlDaSecao(fonteAberta)"
                        :title="fonteAberta.titulo"
                        class="mybp-assistente-secao-frame"
                    ></iframe>
                </template>
            </modal-component>
        </teleport>
    </div>
</template>

<script setup>
import { ref, computed, watch, nextTick, onMounted, onBeforeUnmount } from 'vue'
import AssistenteManualMensagem from './AssistenteManualMensagem.vue'
import ModalComponent from '../Modal'
import { perguntarAoAssistente } from '../../services/assistenteManualService'

const props = defineProps({
    usuarioId: {
        type: [Number, String],
        default: '',
    },
    // Base da rota que devolve uma seção isolada do manual (AjudaController@secao).
    secaoUrl: {
        type: String,
        default: '',
    },
})

const MAX_CARACTERES = 500
const MAX_MENSAGENS_GUARDADAS = 40
// Deve acompanhar ResponderDuvidaManual::MAX_MENSAGENS_HISTORICO.
const MAX_HISTORICO_ENVIADO = 6
const EVENTO_ABRIR = 'mybp:assistente-manual:abrir'
const SUGESTOES = [
    'Como abrir um chamado?',
    'Como aprovar uma requisição de vaga?',
    'Como enviar arquivos no Cloud?',
    'Como alterar minha senha?',
]

// A conversa fica só no navegador (por aba e por usuário) para sobreviver à troca de tela.
const chave = `mybp_assistente_manual_v1_${props.usuarioId}`

const aberto = ref(false)
const enviando = ref(false)
const texto = ref('')
const mensagens = ref([])
const lista = ref(null)
const campo = ref(null)
const modalSecao = ref(null)
const fonteAberta = ref(null)

const podeEnviar = computed(() => !enviando.value && texto.value.trim().length >= 2)

function restaurar() {
    try {
        const salvo = JSON.parse(sessionStorage.getItem(chave) || 'null')
        if (salvo && Array.isArray(salvo.mensagens)) {
            mensagens.value = salvo.mensagens
            aberto.value = Boolean(salvo.aberto)
        }
    } catch (e) {
        mensagens.value = []
    }
}

function salvar() {
    try {
        sessionStorage.setItem(chave, JSON.stringify({
            aberto: aberto.value,
            mensagens: mensagens.value.slice(-MAX_MENSAGENS_GUARDADAS),
        }))
    } catch (e) {
        // Armazenamento indisponível: a conversa só não persiste entre telas.
    }
}

function adicionar(mensagem) {
    mensagens.value.push({ id: `${Date.now()}-${mensagens.value.length}`, ...mensagem })
}

function rolarParaOFim() {
    nextTick(() => {
        if (lista.value) {
            lista.value.scrollTop = lista.value.scrollHeight
        }
    })
}

function ajustarAltura() {
    const elemento = campo.value
    if (!elemento) {
        return
    }
    elemento.style.height = 'auto'
    elemento.style.height = `${Math.min(elemento.scrollHeight, 96)}px`
}

function focarCampo() {
    nextTick(() => campo.value && campo.value.focus())
}

function historicoParaEnvio() {
    return mensagens.value
        .filter((mensagem) => !mensagem.erro)
        .slice(-MAX_HISTORICO_ENVIADO)
        .map(({ papel, texto: conteudo }) => ({ papel, texto: conteudo }))
}

async function enviar(sugestao) {
    const pergunta = (sugestao || texto.value).trim()
    if (enviando.value || pergunta.length < 2) {
        return
    }

    const historico = historicoParaEnvio()
    adicionar({ papel: 'usuario', texto: pergunta })
    texto.value = ''
    enviando.value = true
    nextTick(ajustarAltura)
    rolarParaOFim()

    try {
        const resultado = await perguntarAoAssistente(pergunta, historico)
        adicionar({ papel: 'assistente', texto: resultado.resposta, fontes: resultado.fontes || [] })
    } catch (error) {
        adicionar({ papel: 'assistente', texto: error.message, erro: true })
    } finally {
        enviando.value = false
        rolarParaOFim()
        focarCampo()
    }
}

function urlDaSecao(fonte) {
    return `${props.secaoUrl}/${encodeURIComponent(fonte.ancora)}`
}

function abrirFonte(fonte) {
    fonteAberta.value = fonte
    nextTick(() => modalSecao.value && modalSecao.value.abrirModal())
}

function novaConversa() {
    mensagens.value = []
    focarCampo()
}

function abrir() {
    aberto.value = true
}

function fechar() {
    aberto.value = false
}

function alternar() {
    aberto.value = !aberto.value
}

watch(aberto, (valor) => {
    salvar()
    if (valor) {
        rolarParaOFim()
        focarCampo()
    }
})

watch(mensagens, salvar, { deep: true })

onMounted(() => {
    restaurar()
    window.addEventListener(EVENTO_ABRIR, abrir)
    if (aberto.value) {
        rolarParaOFim()
    }
})

onBeforeUnmount(() => {
    window.removeEventListener(EVENTO_ABRIR, abrir)
})
</script>
