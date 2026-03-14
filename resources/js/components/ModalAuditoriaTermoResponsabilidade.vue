<!--
  Modal reutilizável para ações com auditoria e termo de responsabilidade.
  Uso: import ModalAuditoriaTermoResponsabilidade from '@/components/ModalAuditoriaTermoResponsabilidade.vue'
  - Defina :texto-termo (HTML) no pai ou use slot. Ao abrir, chame this.$refs.xxx.abrir().
  - Em @confirmar recebe { motivo }. Em @fechou pode limpar estado no pai.
  - Slots: #conteudo-antecipado (acima do motivo), #intro (texto antes do textarea), #conteudo-pos-termo (após o termo).
-->
<template>
    <modal
        :ref="refName"
        :id="id"
        :titulo="titulo"
        size="g"
        :mostrar-botao-fechar-no-rodape="false"
        @fechou="onFechou">
        <template #conteudo>
            <slot name="conteudo-antecipado"></slot>
            <fieldset>
                <legend>Sobre a ação</legend>
                <p class="mb-2" v-if="$slots.intro || introText">
                    <slot name="intro">{{ introText }}</slot>
                </p>
                <div class="form-group">
                    <label :for="inputIdMotivo">{{ labelMotivo }} <span class="text-danger">*</span></label>
                    <textarea
                        :id="inputIdMotivo"
                        class="form-control"
                        :rows="motivoRows"
                        v-model="motivoLocal"
                        :placeholder="placeholderMotivo"
                        :maxlength="motivoMaxLength"></textarea>
                    <small class="text-muted">{{ (motivoLocal || '').length }}/{{ motivoMaxLength }}</small>
                </div>
            </fieldset>
            <fieldset class="mt-3">
                <legend>{{ legendTermo }}</legend>
                <div class="termo-responsabilidade-texto mb-2" v-html="textoTermo"></div>
                <div class="form-check mt-3">
                    <input
                        type="checkbox"
                        :id="inputIdAceite"
                        class="form-check-input"
                        v-model="aceiteLocal">
                    <label class="form-check-label" :for="inputIdAceite">
                        {{ labelAceite }}
                    </label>
                </div>
            </fieldset>
            <slot name="conteudo-pos-termo"></slot>
        </template>
        <template #rodape>
            <button
                type="button"
                class="btn btn-sm btn-secondary mr-1"
                @click="cancelar"
                :disabled="loading">
                {{ labelBotaoCancelar }}
            </button>
            <button
                type="button"
                class="btn btn-sm btn-primary"
                @click="confirmar"
                :disabled="loading || !podeConfirmar">
                <i class="fa fa-spinner fa-pulse" v-if="loading"></i>
                <i class="fa fa-check" v-else></i>
                {{ labelBotaoConfirmar }}
            </button>
        </template>
    </modal>
</template>

<script>
import { defineComponent } from 'vue'
import Modal from './Modal.vue'

export default defineComponent({
    name: 'ModalAuditoriaTermoResponsabilidade',

    components: { Modal },

    emits: ['confirmar', 'cancelar', 'fechou'],

    props: {
        /** Id único da modal (obrigatório para o Modal interno) */
        id: {
            type: String,
            required: true
        },
        /** Ref name para a modal interna (permite abrir via $refs) */
        refName: {
            type: String,
            default: 'modalAuditoria'
        },
        /** Título da modal */
        titulo: {
            type: String,
            default: 'Auditoria'
        },
        /** Texto do termo de responsabilidade (HTML) */
        textoTermo: {
            type: String,
            default: ''
        },
        /** Legend do fieldset do termo */
        legendTermo: {
            type: String,
            default: 'Termo de responsabilidade'
        },
        /** Label do campo motivo */
        labelMotivo: {
            type: String,
            default: 'Motivo'
        },
        /** Placeholder do textarea motivo */
        placeholderMotivo: {
            type: String,
            default: 'Informe o motivo.'
        },
        /** Texto opcional exibido antes do campo motivo (intro) */
        introText: {
            type: String,
            default: ''
        },
        /** Label do checkbox de aceite do termo */
        labelAceite: {
            type: String,
            default: 'Declaro que li e estou ciente do termo de responsabilidade acima e assumo a responsabilidade por esta ação.'
        },
        /** Limite de caracteres do motivo */
        motivoMaxLength: {
            type: Number,
            default: 1000
        },
        /** Número de linhas do textarea motivo */
        motivoRows: {
            type: Number,
            default: 4
        },
        /** Label do botão confirmar */
        labelBotaoConfirmar: {
            type: String,
            default: 'Confirmar'
        },
        /** Label do botão cancelar */
        labelBotaoCancelar: {
            type: String,
            default: 'Cancelar'
        },
        /** Indica carregamento (desabilita botões) */
        loading: {
            type: Boolean,
            default: false
        }
    },

    data() {
        return {
            motivoLocal: '',
            aceiteLocal: false
        }
    },

    computed: {
        inputIdMotivo() {
            return `${this.id}_motivo`
        },
        inputIdAceite() {
            return `${this.id}_aceite`
        },
        podeConfirmar() {
            return (this.motivoLocal || '').trim().length > 0 && this.aceiteLocal
        }
    },

    methods: {
        abrir() {
            this.motivoLocal = ''
            this.aceiteLocal = false
            const modal = this.$refs[this.refName]
            if (modal && typeof modal.abrirModal === 'function') {
                modal.abrirModal()
            }
        },
        fechar() {
            const modal = this.$refs[this.refName]
            if (modal && typeof modal.fecharModal === 'function') {
                modal.fecharModal()
            }
        },
        cancelar() {
            this.$emit('cancelar')
            this.fechar()
        },
        confirmar() {
            if (!this.podeConfirmar || this.loading) return
            const motivo = (this.motivoLocal || '').trim()
            this.$emit('confirmar', { motivo })
        },
        onFechou() {
            this.$emit('fechou')
        }
    }
})
</script>

<style scoped>
.termo-responsabilidade-texto {
    font-size: 0.85rem;
    line-height: 1.5;
}
.termo-responsabilidade-texto :deep(p) {
    font-size: 0.85rem;
    line-height: 1.5;
    margin-bottom: 0.5rem;
}
</style>
