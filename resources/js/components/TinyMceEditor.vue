<template>
    <!-- Host estável: TinyMCE não pode substituir o root do Vue (quebra insertBefore/__vnode) -->
    <div ref="host" class="tiny-mce-host"></div>
</template>

<script>
import tinymceSelfhost from '../utils/tinymceSelfhost'
import { attachWeeklyMentions } from './weekly-report/tinyMentions'

const selfhost = tinymceSelfhost && tinymceSelfhost.loadTinyMce ? tinymceSelfhost : (tinymceSelfhost && tinymceSelfhost.default) || tinymceSelfhost
const { getTinyMceInit, loadTinyMce } = selfhost

let instanceSeq = 0

export default {
    name: 'TinyMceEditor',
    emits: ['update:modelValue', 'mention'],
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
        },
        mentionsUrl: {
            type: String,
            default: ''
        }
    },
    data() {
        return {
            editorId: this.id || `tiny-mce-${++instanceSeq}-${Date.now().toString(36)}`,
            editor: null,
            syncingFromParent: false,
            forceContentSync: false,
            lastEmitted: null,
            detachMentions: null,
            _mounting: false,
            _textarea: null
        }
    },
    watch: {
        modelValue(val) {
            if (!this.editor || this.syncingFromParent) {
                return
            }
            const next = val || ''
            if (next === this.lastEmitted) {
                return
            }
            const current = this.editor.getContent()
            if (next === current) {
                this.lastEmitted = next
                return
            }
            if (
                !this.forceContentSync &&
                typeof this.editor.hasFocus === 'function' &&
                this.editor.hasFocus()
            ) {
                return
            }
            this.syncingFromParent = true
            this.editor.setContent(next)
            this.lastEmitted = next
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
        if (this.editor && !this.syncingFromParent) {
            const html = this.editor.getContent()
            if (html !== (this.modelValue || '')) {
                this.$emit('update:modelValue', html)
            }
        }
        this.desmontarEditor()
    },
    methods: {
        clearContent() {
            this.forceContentSync = true
            this.lastEmitted = ''
            this.syncingFromParent = true
            if (this.editor) {
                this.editor.setContent('')
            }
            this.syncingFromParent = false
            this.forceContentSync = false
            this.$emit('update:modelValue', '')
        },
        setContentHtml(html) {
            const next = html || ''
            this.forceContentSync = true
            this.lastEmitted = next
            this.syncingFromParent = true
            if (this.editor) {
                this.editor.setContent(next)
            }
            this.syncingFromParent = false
            this.forceContentSync = false
            this.$emit('update:modelValue', next)
        },
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
            return true
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
            if (this.editor || this._mounting || !this.$refs.host) {
                return
            }
            if (this.elementoVisivel(this.$refs.host)) {
                this.montarEditor()
                return
            }
            this.cancelarAgendamento()
            this._visibilityObserver = new MutationObserver(() => {
                if (this.$refs.host && this.elementoVisivel(this.$refs.host)) {
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
                if (!this.$refs.host) {
                    this.cancelarAgendamento()
                    return
                }
                if (this.elementoVisivel(this.$refs.host)) {
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
                this.lastEmitted = html
                this.$emit('update:modelValue', html)
            } else {
                this.lastEmitted = html
            }
        },
        criarTextarea() {
            const host = this.$refs.host
            if (!host) return null
            host.innerHTML = ''
            const textarea = document.createElement('textarea')
            textarea.id = this.editorId
            textarea.setAttribute('rows', '4')
            host.appendChild(textarea)
            this._textarea = textarea
            return textarea
        },
        async montarEditor() {
            if (this.editor || this._mounting) {
                return
            }
            this._mounting = true
            try {
                const tinymce = await loadTinyMce()
                const host = this.$refs.host
                if (!host) {
                    return
                }
                if (!this.elementoVisivel(host)) {
                    this._mounting = false
                    this.agendarMontagem()
                    return
                }
                if (this.editor) {
                    return
                }

                const textarea = this.criarTextarea()
                if (!textarea) {
                    return
                }

                const merged = getTinyMceInit(this.preset, this.init)
                const originalSetup = merged.setup

                await tinymce.init({
                    ...merged,
                    target: textarea,
                    readonly: this.disabled,
                    setup: (ed) => {
                        this.editor = ed
                        if (typeof originalSetup === 'function') {
                            originalSetup(ed)
                        }
                        if (this.mentionsUrl) {
                            this.detachMentions = attachWeeklyMentions(ed, {
                                searchUrl: this.mentionsUrl,
                                onSelect: (user) => this.$emit('mention', user)
                            })
                        }
                        ed.on('init', () => {
                            this.syncingFromParent = true
                            const initial = this.modelValue || ''
                            ed.setContent(initial)
                            this.lastEmitted = ed.getContent()
                            this.syncingFromParent = false
                            if (this.disabled && ed.setMode) {
                                ed.setMode('readonly')
                            }
                            // Evita scrollIntoView do iframe no meio do modal
                            try {
                                if (typeof ed.hasFocus === 'function' && ed.hasFocus()) {
                                    ed.fire('blur')
                                }
                                const active = document.activeElement
                                if (active && this.$el && this.$el.contains(active)) {
                                    active.blur()
                                }
                            } catch (e) {
                                /* ignore */
                            }
                        })
                        ed.on('change keyup undo redo input blur', () => this.emitContent())
                    }
                })
            } catch (err) {
                if (typeof console !== 'undefined' && console.error) {
                    console.error('TinyMCE self-host:', err)
                }
                if (!this.editor) {
                    this.$nextTick(() => this.agendarMontagem())
                }
            } finally {
                this._mounting = false
            }
        },
        desmontarEditor() {
            if (typeof this.detachMentions === 'function') {
                this.detachMentions()
                this.detachMentions = null
            }
            if (this.editor) {
                try {
                    this.editor.remove()
                } catch (e) {
                    /* ignore */
                }
                this.editor = null
            }
            this._textarea = null
            if (this.$refs.host) {
                this.$refs.host.innerHTML = ''
            }
        }
    }
}
</script>
