<template>
    <textarea :id="editorId" ref="textarea"></textarea>
</template>

<script>
import tinymceSelfhost from '../utils/tinymceSelfhost'

const selfhost = tinymceSelfhost && tinymceSelfhost.loadTinyMce ? tinymceSelfhost : (tinymceSelfhost && tinymceSelfhost.default) || tinymceSelfhost
const { getTinyMceInit, loadTinyMce } = selfhost

let instanceSeq = 0

export default {
    name: 'TinyMceEditor',
    emits: ['update:modelValue'],
    props: {
        modelValue: {
            type: String,
            default: ''
        },
        preset: {
            type: String,
            default: 'padrao'
        },
        init: {
            type: Object,
            default: () => ({})
        },
        disabled: {
            type: Boolean,
            default: false
        },
        id: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            editorId: this.id || `tiny-mce-${++instanceSeq}-${Date.now().toString(36)}`,
            editor: null,
            syncingFromParent: false
        }
    },
    watch: {
        modelValue(val) {
            if (!this.editor) {
                return
            }
            const next = val || ''
            if (next === this.editor.getContent()) {
                return
            }
            this.syncingFromParent = true
            this.editor.setContent(next)
            this.syncingFromParent = false
        },
        disabled(val) {
            if (this.editor && this.editor.setMode) {
                this.editor.setMode(val ? 'readonly' : 'design')
            }
        }
    },
    mounted() {
        this.$nextTick(() => this.agendarMontagem())
    },
    beforeUnmount() {
        this.cancelarAgendamento()
        this.desmontarEditor()
    },
    methods: {
        elementoVisivel(el) {
            if (!el || !el.isConnected) {
                return false
            }
            let node = el
            while (node && node !== document.body) {
                const style = window.getComputedStyle(node)
                if (style.display === 'none' || style.visibility === 'hidden') {
                    return false
                }
                node = node.parentElement
            }
            const style = window.getComputedStyle(el)
            return el.offsetParent !== null || style.position === 'fixed'
        },
        cancelarAgendamento() {
            if (this._visibilityObserver) {
                this._visibilityObserver.disconnect()
                this._visibilityObserver = null
            }
            if (this._visibilityInterval) {
                clearInterval(this._visibilityInterval)
                this._visibilityInterval = null
            }
        },
        agendarMontagem() {
            if (this.editor || !this.$refs.textarea) {
                return
            }
            if (this.elementoVisivel(this.$refs.textarea)) {
                this.montarEditor()
                return
            }
            this.cancelarAgendamento()
            this._visibilityObserver = new MutationObserver(() => {
                if (this.elementoVisivel(this.$refs.textarea)) {
                    this.cancelarAgendamento()
                    this.montarEditor()
                }
            })
            this._visibilityObserver.observe(document.body, {
                attributes: true,
                subtree: true,
                attributeFilter: ['class', 'style', 'aria-hidden']
            })
            this._visibilityInterval = setInterval(() => {
                if (!this.$refs.textarea) {
                    this.cancelarAgendamento()
                    return
                }
                if (this.elementoVisivel(this.$refs.textarea)) {
                    this.cancelarAgendamento()
                    this.montarEditor()
                }
            }, 150)
        },
        emitContent() {
            if (!this.editor || this.syncingFromParent) {
                return
            }
            const html = this.editor.getContent()
            if (html !== (this.modelValue || '')) {
                this.$emit('update:modelValue', html)
            }
        },
        async montarEditor() {
            try {
                const tinymce = await loadTinyMce()
                if (!this.$refs.textarea || !this.elementoVisivel(this.$refs.textarea)) {
                    return
                }

                const merged = getTinyMceInit(this.preset, this.init)
                const originalSetup = merged.setup

                await tinymce.init({
                    ...merged,
                    target: this.$refs.textarea,
                    readonly: this.disabled,
                    setup: (ed) => {
                        this.editor = ed
                        if (typeof originalSetup === 'function') {
                            originalSetup(ed)
                        }
                        ed.on('init', () => {
                            this.syncingFromParent = true
                            ed.setContent(this.modelValue || '')
                            this.syncingFromParent = false
                            if (this.disabled && ed.setMode) {
                                ed.setMode('readonly')
                            }
                        })
                        ed.on('change keyup undo redo', () => this.emitContent())
                    }
                })
            } catch (err) {
                if (typeof console !== 'undefined' && console.error) {
                    console.error('TinyMCE self-host:', err)
                }
            }
        },
        desmontarEditor() {
            if (this.editor) {
                this.editor.remove()
                this.editor = null
            }
        }
    }
}
</script>
