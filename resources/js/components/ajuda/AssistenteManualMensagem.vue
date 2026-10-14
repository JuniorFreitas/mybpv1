<template>
    <div class="mybp-assistente-msg" :class="'mybp-assistente-msg--' + mensagem.papel">
        <div v-if="mensagem.papel === 'assistente'" class="mybp-assistente-avatar" aria-hidden="true">
            <i class="bx bx-bot"></i>
        </div>
        <div class="mybp-assistente-balao" :class="{ 'mybp-assistente-balao--erro': mensagem.erro }">
            <div v-if="mensagem.papel === 'assistente'" class="mybp-assistente-texto" v-html="html"></div>
            <div v-else class="mybp-assistente-texto">{{ mensagem.texto }}</div>

            <div v-if="fontes.length" class="mybp-assistente-fontes">
                <span class="mybp-assistente-fontes-titulo">Saiba mais</span>
                <button
                    v-for="fonte in fontes"
                    :key="fonte.ancora"
                    type="button"
                    class="mybp-assistente-fonte"
                    :title="fonte.caminho_menu ? 'Menu: ' + fonte.caminho_menu : 'Ver a explicação completa'"
                    @click="emit('abrir-fonte', fonte)"
                >
                    <i class="bx bx-book-open" aria-hidden="true"></i>
                    <span class="mybp-assistente-fonte-titulo">{{ fonte.titulo }}</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { markdownSeguro } from '../../utils/markdownSeguro'

const emit = defineEmits(['abrir-fonte'])

const props = defineProps({
    mensagem: {
        type: Object,
        required: true,
    },
})

const html = computed(() => markdownSeguro(props.mensagem.texto))
const fontes = computed(() => props.mensagem.fontes || [])
</script>
