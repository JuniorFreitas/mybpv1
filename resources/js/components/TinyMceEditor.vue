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
        this.montarEditor()
    },
    beforeUnmount() {
        this.desmontarEditor()
    },
    methods: {
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
                if (!this.$refs.textarea) {
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
