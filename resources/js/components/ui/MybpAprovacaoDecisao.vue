<template>
    <fieldset
        class="mybp-modal-secao mybp-aprovacao-decisao"
        :class="{ 'mybp-aprovacao-decisao--ativa': editavel }"
    >
        <legend>{{ titulo }}</legend>

        <div v-if="!editavel && temResumo" class="mybp-aprovacao-decisao__resumo">
            <mybp-status-badge :variante="badgeVariante" :texto="statusLabel" />
            <p v-if="nome" class="mybp-aprovacao-decisao__meta mb-0">
                por {{ nome }}<template v-if="data"> · {{ data }}</template>
            </p>
            <p v-if="modelValue" class="mybp-aprovacao-decisao__obs mb-0">
                {{ modelValue }}
            </p>
        </div>

        <div v-else-if="editavel" class="mybp-aprovacao-decisao__corpo">
            <p class="mybp-aprovacao-decisao__hint mb-2">
                Preencha a observação se desejar e use <strong>Aprovar</strong> ou <strong>Reprovar</strong> no rodapé.
            </p>
            <div class="form-group mybp-filtro-campo mb-0">
                <label class="mybp-label" :for="inputId">
                    Observação
                    <span class="text-muted font-weight-normal">
                        — opcional ao aprovar · obrigatória ao reprovar
                    </span>
                </label>
                <textarea
                    :id="inputId"
                    class="form-control form-control-sm"
                    :class="{ 'is-invalid': !!erro }"
                    rows="3"
                    :value="modelValue"
                    :disabled="disabled"
                    placeholder="Comentário da decisão (pode informar também ao aprovar)"
                    @input="$emit('update:modelValue', $event.target.value)"
                ></textarea>
                <div v-if="erro" class="invalid-feedback d-block">{{ erro }}</div>
            </div>
        </div>

        <div v-else class="text-muted small mb-0">
            Aguardando decisão nesta etapa.
        </div>
    </fieldset>
</template>

<script>
import MybpStatusBadge from './MybpStatusBadge.vue'

export default {
    name: 'MybpAprovacaoDecisao',
    components: { MybpStatusBadge },
    props: {
        titulo: { type: String, required: true },
        modelValue: { type: String, default: '' },
        editavel: { type: Boolean, default: false },
        disabled: { type: Boolean, default: false },
        status: { type: String, default: '' },
        nome: { type: String, default: '' },
        data: { type: String, default: '' },
        erro: { type: String, default: '' },
        inputId: { type: String, default: '' }
    },
    emits: ['update:modelValue'],
    computed: {
        temResumo() {
            const s = String(this.status || '').toLowerCase()
            return s === 'aprovado' || s === 'reprovado' || !!this.nome
        },
        statusNormalizado() {
            return String(this.status || '').toLowerCase()
        },
        statusLabel() {
            if (this.statusNormalizado === 'aprovado') return 'Aprovado'
            if (this.statusNormalizado === 'reprovado') return 'Reprovado'
            return this.status || 'Pendente'
        },
        badgeVariante() {
            if (this.statusNormalizado === 'aprovado') return 'aprovado'
            if (this.statusNormalizado === 'reprovado') return 'reprovado'
            return 'pendente'
        }
    }
}
</script>
