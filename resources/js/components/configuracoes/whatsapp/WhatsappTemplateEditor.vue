<template>
    <div class="whatsapp-template-editor">
        <div v-if="!disabled" class="editor-toolbar btn-toolbar mb-2 p-2 border rounded bg-light" role="toolbar">
            <div class="btn-group btn-group-sm mr-2 mb-1" role="group" aria-label="Formatação WhatsApp">
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    title="Negrito — *texto*"
                    @click="aplicarFormato('*', '*')"
                >
                    <i class="fas fa-bold"></i>
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    title="Itálico (_texto_) — o WhatsApp não possui sublinhado nativo"
                    @click="aplicarFormato('_', '_')"
                >
                    <i class="fas fa-italic"></i>
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    title="Tachado — ~texto~"
                    @click="aplicarFormato('~', '~')"
                >
                    <i class="fas fa-strikethrough"></i>
                </button>
                <button
                    type="button"
                    class="btn btn-outline-secondary"
                    title="Monoespaçado — ```texto```"
                    @click="aplicarFormato('```', '```')"
                >
                    <i class="fas fa-code"></i>
                </button>
            </div>
            <div class="btn-group btn-group-sm mb-1" role="group">
                <button
                    type="button"
                    class="btn btn-outline-success"
                    :class="{ active: emojiAberto }"
                    title="Inserir emoji"
                    @click="emojiAberto = !emojiAberto"
                >
                    <i class="far fa-smile"></i> Emoji
                </button>
            </div>
            <small class="text-muted d-block w-100 mt-1">
                WhatsApp: <code>*negrito*</code>, <code>_itálico_</code>, <code>~tachado~</code>, <code>```mono```</code>.
                Não há sublinhado nativo — use <code>_itálico_</code> para ênfase.
            </small>
        </div>

        <div v-if="emojiAberto && !disabled" class="emoji-panel border rounded p-2 mb-2 bg-white">
            <div class="mb-2">
                <small class="text-muted font-weight-bold">Frequentes</small>
                <div class="emoji-grid">
                    <button
                        v-for="emoji in emojisFrequentes"
                        :key="'f-' + emoji"
                        type="button"
                        class="btn btn-light btn-sm emoji-btn"
                        @click="inserirEmoji(emoji)"
                    >{{ emoji }}</button>
                </div>
            </div>
            <div>
                <small class="text-muted font-weight-bold">RH / Comunicação</small>
                <div class="emoji-grid">
                    <button
                        v-for="emoji in emojisRh"
                        :key="'r-' + emoji"
                        type="button"
                        class="btn btn-light btn-sm emoji-btn"
                        @click="inserirEmoji(emoji)"
                    >{{ emoji }}</button>
                </div>
            </div>
        </div>

        <textarea
            ref="textareaRef"
            class="form-control whatsapp-editor-textarea"
            :rows="rows"
            :disabled="disabled"
            :maxlength="maxlength"
            :value="modelValue"
            @input="onInput"
        ></textarea>
        <small v-if="maxlength" class="text-muted">{{ (modelValue || '').length }} / {{ maxlength }} caracteres</small>
    </div>
</template>

<script>
import { defineComponent, ref, nextTick } from 'vue'

export default defineComponent({
    name: 'WhatsappTemplateEditor',
    props: {
        modelValue: { type: String, default: '' },
        disabled: { type: Boolean, default: false },
        maxlength: { type: Number, default: 4096 },
        rows: { type: Number, default: 14 },
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {
        const textareaRef = ref(null)
        const emojiAberto = ref(false)

        const emojisFrequentes = [
            '👋', '👏', '👏🏽', '🎉', '✅', '❌', '⚠️', '☺️', '😊', '🙏', '💼', '📋',
        ]

        const emojisRh = [
            '📆', '📍', '🏥', '📞', '🗓️', '🚌', '📌', '⬇️', '🔗', '📝', '✉️', '🕐',
        ]

        const onInput = (event) => {
            emit('update:modelValue', event.target.value)
        }

        const inserirNoCursor = (texto) => {
            const el = textareaRef.value
            if (!el || props.disabled) {
                emit('update:modelValue', (props.modelValue || '') + texto)
                return
            }

            const start = el.selectionStart ?? (props.modelValue || '').length
            const end = el.selectionEnd ?? start
            const atual = props.modelValue || ''
            const novo = atual.substring(0, start) + texto + atual.substring(end)

            emit('update:modelValue', novo)

            nextTick(() => {
                el.focus()
                const pos = start + texto.length
                el.setSelectionRange(pos, pos)
            })
        }

        const aplicarFormato = (prefixo, sufixo) => {
            const el = textareaRef.value
            if (!el || props.disabled) return

            const start = el.selectionStart ?? 0
            const end = el.selectionEnd ?? 0
            const atual = props.modelValue || ''
            const selecionado = atual.substring(start, end) || 'texto'
            const novo = atual.substring(0, start) + prefixo + selecionado + sufixo + atual.substring(end)

            emit('update:modelValue', novo)

            nextTick(() => {
                el.focus()
                const selStart = start + prefixo.length
                const selEnd = selStart + selecionado.length
                el.setSelectionRange(selStart, selEnd)
            })
        }

        const inserirEmoji = (emoji) => {
            inserirNoCursor(emoji)
        }

        return {
            textareaRef,
            emojiAberto,
            emojisFrequentes,
            emojisRh,
            onInput,
            aplicarFormato,
            inserirEmoji,
        }
    },
})
</script>

<style scoped>
.emoji-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    margin-top: 4px;
}

.emoji-btn {
    font-size: 1.25rem;
    line-height: 1.2;
    min-width: 2.25rem;
    padding: 0.15rem 0.35rem;
}

.whatsapp-editor-textarea {
    font-family: inherit;
    line-height: 1.5;
}

.editor-toolbar .btn.active {
    background-color: #28a745;
    color: #fff;
    border-color: #28a745;
}
</style>
