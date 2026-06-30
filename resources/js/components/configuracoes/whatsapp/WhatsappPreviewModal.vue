<template>
    <teleport to="body">
        <div
            v-if="modelValue"
            class="modal-backdrop fade show"
            @click="fechar"
        ></div>
        <div
            class="modal fade"
            :class="{ show: modelValue }"
            :style="{ display: modelValue ? 'block' : 'none' }"
            tabindex="-1"
            role="dialog"
            @click.self="fechar"
        >
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Preview da mensagem WhatsApp</h5>
                        <button type="button" class="close" @click="fechar"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <preload v-if="loading" class="text-center" />
                        <div v-else-if="erro" class="alert alert-danger">{{ erro }}</div>
                        <div
                            v-else
                            class="whatsapp-preview p-3 bg-light border rounded"
                            style="white-space: pre-wrap; font-family: inherit; line-height: 1.5;"
                            v-html="mensagemHtml"
                        ></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" @click="fechar">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>

<script>
import { defineComponent, ref, watch, computed, onUnmounted } from 'vue'
import axios from 'axios'
import { whatsappFormatoParaHtml } from './whatsappFormato.js'

export default defineComponent({
    name: 'WhatsappPreviewModal',
    props: {
        modelValue: { type: Boolean, default: false },
        tipoMensagem: { type: String, default: '' },
        contexto: { type: Object, default: () => ({}) },
        urlPreview: { type: String, default: '' },
        corpoEdicao: { type: String, default: null },
        empresaId: { type: [Number, String], default: null },
    },
    emits: ['update:modelValue'],
    setup(props, { emit }) {
        const loading = ref(false)
        const mensagem = ref('')
        const mensagemHtml = computed(() => whatsappFormatoParaHtml(mensagem.value))
        const erro = ref('')

        const fechar = () => emit('update:modelValue', false)

        const toggleBodyScroll = (aberto) => {
            document.body.classList.toggle('modal-open', aberto)
        }

        const carregar = async () => {
            if (!props.modelValue) return

            loading.value = true
            erro.value = ''
            mensagem.value = ''

            try {
                let response

                if (props.urlPreview) {
                    response = await axios.get(props.urlPreview)
                    mensagem.value = response.data.mensagem || response.data.msg || ''
                } else if (props.tipoMensagem && props.corpoEdicao !== null) {
                    response = await axios.post(`/g/configuracoes/whatsapp/templates/${props.tipoMensagem}/preview`, {
                        corpo: props.corpoEdicao,
                        contexto: props.contexto,
                    }, {
                        params: props.empresaId ? { empresa_id: Number(props.empresaId) } : {},
                    })
                    mensagem.value = response.data.mensagem || ''
                } else if (props.tipoMensagem) {
                    response = await axios.post('/g/configuracoes/whatsapp/preview-fluxo', {
                        tipo_mensagem: props.tipoMensagem,
                        contexto: props.contexto,
                        empresa_id: props.empresaId ? Number(props.empresaId) : undefined,
                    })
                    mensagem.value = response.data.mensagem || ''
                } else {
                    erro.value = 'Tipo de mensagem não informado.'
                }
            } catch (e) {
                erro.value = e.response?.data?.msg || e.response?.data?.message || 'Erro ao carregar preview.'
            } finally {
                loading.value = false
            }
        }

        watch(() => props.modelValue, (aberto) => {
            toggleBodyScroll(aberto)
            if (aberto) carregar()
        })

        onUnmounted(() => toggleBodyScroll(false))

        return { loading, mensagem, mensagemHtml, erro, fechar }
    },
})
</script>
