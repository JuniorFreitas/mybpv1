<template>
    <div>
        <div class="modal fade" tabindex="-1" role="dialog" :id="id" data-backdrop="static" data-keyboard="false" :key="modalKey">
            <div class="modal-dialog" :class="[tamanho, central]" role="document" :style="styles">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="col-10">
                            <slot v-if="topo" name="topo"> </slot>
                            <h5 v-else class="modal-title">{{ titulo }}</h5>
                        </div>
                        <div class="col-2">
                            <button v-if="exibirFechar" aria-label="Close" class="close" type="button" @click="fecharModal">
                                <span aria-hidden="true" class="btClose">&times;</span>
                            </button>
                        </div>
                    </div>
                    <div class="modal-body">
                        <i class="fa fa-spinner" v-if="preload"></i> <span v-if="textoPreload !== ''">{{ textoPreload }}</span>

                        <div v-if="!preload">
                            <slot name="conteudo"></slot>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm mr-1 btn-outline-secondary" v-if="exibirFechar && mostrarBotaoFecharNoRodape" @click="fecharModal">
                            {{ labelFechar }}
                        </button>
                        <slot name="rodape"></slot>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { defineComponent } from 'vue'

export default defineComponent({
    name: 'Modal',

    emits: ['fechou', 'abriu'],

    props: {
        id: {
            type: String,
            required: true
        },

        modalPai: {
            type: String,
            required: false
        },

        topo: {
            type: Boolean,
            required: false,
            default: false
        },

        titulo: {
            type: String,
            required: true,
            default: 'Titulo da Janela'
        },

        fechar: {
            type: Boolean,
            required: false,
            default: true
        },

        size: {
            type: [String, Number],
            required: false,
            default: ''
        },

        centralizada: {
            type: Boolean,
            required: false,
            default: false
        },

        labelFechar: {
            type: String,
            required: false,
            default: 'Fechar'
        },
        mostrarBotaoFecharNoRodape: {
            type: Boolean,
            required: false,
            default: true
        },
        drag: {
            type: Boolean,
            required: false,
            default: true
        }
    },

    data: function () {
        return {
            textoPreload: '',
            preload: false,
            tela: window.innerWidth,
            zIndex: 0,
            modalKey: 0,  // alterado ao fechar para recriar o DOM da modal e evitar scroll no background
            _resizeHandler: null
        }
    },
    methods: {
        /** Fecha esta modal: remove só o backdrop desta (por id), esconde a modal; se ainda houver outras abertas, body continua modal-open. */
        fecharModal() {
            const active = document.activeElement
            if (active && this.$el.contains(active)) {
                active.blur()
            }
            const $minhaModal = $('#' + this.id)
            if (!$minhaModal.length) {
                this.$emit('fechou', {})
                return
            }
            const backdropId = 'modal-backdrop-' + this.id
            $(`#${backdropId}`).remove()
            $minhaModal.removeClass('show').css('display', 'none').attr('aria-hidden', 'true')
            if ($('.modal.show').length === 0) {
                $('body').removeClass('modal-open').css('overflow', '')
            } else {
                $('body').addClass('modal-open').css('overflow', 'hidden')
            }
            this.modalKey += 1
            this.$emit('fechou', {})
        },

        abrirModal() {
            $('#' + this.id).modal('show')
            this.$emit('abriu', {})
        }
    },

    mounted: function () {
        let self = this
        // Delegação no container para que os eventos funcionem também no novo elemento após recriar a modal (modalKey++)
        let $container = $(this.$el)

        // Garante que o body não role quando a modal estiver aberta (evita scroll no background ao reabrir)
        $container.on('show.bs.modal', '.modal', function (event) {
            let modalEl = this
            $('body').addClass('modal-open').css('overflow', 'hidden')
            var zIndex = 1040 + 10 * $('.modal:visible').length
            self.zIndex = zIndex
            $(modalEl).css('z-index', zIndex)
            setTimeout(() => {
                // Backdrop que o Bootstrap acabou de criar: marca com id pelo id da modal para remover só este ao fechar
                var $backdrops = $('.modal-backdrop').not('.modal-stack')
                var $nossoBackdrop = $backdrops.last()
                var backdropId = 'modal-backdrop-' + modalEl.id
                $backdrops.css('z-index', zIndex - 1).addClass('modal-stack')
                $nossoBackdrop.attr('id', backdropId)

                if ($(modalEl).next('.modal-backdrop').length === 0) {
                    var $backdropEl = $('#' + backdropId)
                    if ($backdropEl.length) $backdropEl.insertAfter(modalEl)
                }
            }, 50)
        })

        // Ao fechar via Bootstrap (ex.: clique no backdrop, Escape): remove só o backdrop desta modal; mantém body modal-open se ainda houver outras abertas
        $container.on('hidden.bs.modal', '.modal', function (event) {
            var modalId = event.target.id
            if (modalId) {
                $('#modal-backdrop-' + modalId).remove()
            }
            if ($('.modal.show').length > 0) {
                $('body').addClass('modal-open').css('overflow', 'hidden')
            } else {
                $('body').removeClass('modal-open').css('overflow', '')
            }
            if (self.id === modalId) {
                self.modalKey += 1
                self.$emit('fechou', {})
            }
        })

        this._resizeHandler = () => {
            this.tela = window.innerWidth
        }
        window.addEventListener('resize', this._resizeHandler)
    },

    beforeUnmount() {
        if (this._resizeHandler) {
            window.removeEventListener('resize', this._resizeHandler)
        }
    },

    computed: {
        styles: function () {
            // caso passe numero, retorna esse objeto de styles
            if (typeof this.size === 'number' && this.tela >= 710) {
                // 710 é o tamanho de tablet
                return {
                    'max-width': this.size + '%'
                }
            }
        },
        exibirFechar: function () {
            return this.fechar !== undefined ? this.fechar : true
        },

        central: function () {
            return this.centralizada ? 'modal-dialog-centered' : ''
        },

        tamanho: function () {
            if (this.size === undefined || typeof this.size === 'number') {
                return ''
            }
            const valor = String(this.size).toLowerCase()
            switch (valor) {
                case 'p':
                    return 'modal-sm'
                case 'g':
                    return 'modal-lg'
                default:
                    return ''
            }
        }
    }
})
</script>
