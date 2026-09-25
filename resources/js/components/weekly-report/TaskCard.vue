<template>
    <div
        class="card wr-task-card mb-0"
        role="button"
        tabindex="0"
        :class="{
            'wr-task-card--done': tarefa.concluido,
            'wr-task-card--late': isLate
        }"
        @click="$emit('open', tarefa, lista)"
        @keydown.enter.prevent="$emit('open', tarefa, lista)"
        @keydown.space.prevent="$emit('open', tarefa, lista)"
    >
        <div class="card-body p-2">
            <div class="wr-task-card__title" :class="{ 'texto-riscado text-muted': tarefa.concluido }">
                {{ tarefa.titulo }}
            </div>

            <div v-if="temMeta" class="wr-task-card__meta mt-2">
                <span
                    v-if="tarefa.datahora_entrega"
                    class="badge badge-pill mr-1 mb-1"
                    :class="isLate ? 'badge-danger' : 'badge-soft'"
                >
                    <i class="far fa-clock"></i>
                    {{ formatData(tarefa.datahora_entrega) }}
                </span>
                <span v-if="progress.total" class="badge badge-pill badge-soft mr-1 mb-1">
                    <i class="fas fa-check-square"></i>
                    {{ progress.done }}/{{ progress.total }}
                </span>
                <span v-if="anexosCount" class="badge badge-pill badge-soft mr-1 mb-1">
                    <i class="fas fa-paperclip"></i>
                    {{ anexosCount }}
                </span>
                <span v-if="tarefa.bloqueios_count" class="badge badge-pill badge-danger mr-1 mb-1" title="Bloqueios">
                    <i class="fas fa-ban"></i>
                    {{ tarefa.bloqueios_count }}
                </span>
                <span
                    v-else-if="tarefa.comentarios_count"
                    class="badge badge-pill badge-soft mr-1 mb-1"
                    title="Comentários"
                >
                    <i class="far fa-comment"></i>
                    {{ tarefa.comentarios_count }}
                </span>
                <span v-if="tarefa.concluido" class="badge badge-pill badge-success mr-1 mb-1">Concluído</span>
            </div>

            <div v-if="tarefa.membros && tarefa.membros.length" class="wr-task-card__members mt-2">
                <span
                    v-for="m in tarefa.membros.slice(0, 5)"
                    :key="m.id"
                    class="wr-avatar-badge"
                    :title="m.nome"
                >
                    {{ inicialNome(m.nome) }}
                </span>
                <span v-if="tarefa.membros.length > 5" class="wr-avatar-badge wr-avatar-badge--more">
                    +{{ tarefa.membros.length - 5 }}
                </span>
            </div>
        </div>
    </div>
</template>

<script>
import { checklistProgress, inicialNome } from './api'

export default {
    name: 'TaskCard',
    props: {
        tarefa: { type: Object, required: true },
        lista: { type: Object, required: true }
    },
    emits: ['open'],
    computed: {
        progress() {
            return checklistProgress(this.tarefa)
        },
        isLate() {
            if (this.tarefa.concluido || !this.tarefa.datahora_entrega) return false
            if (this.tarefa.emAtraso || this.tarefa.em_atraso) return true
            const value = this.tarefa.datahora_entrega
            if (typeof moment === 'undefined') return false
            const m = moment(value, ['DD/MM/YYYY [às] HH:mm', 'YYYY-MM-DD HH:mm:ss', 'DD/MM/YYYY HH:mm'], true)
            return m.isValid() && m.isBefore(moment())
        },
        anexosCount() {
            return Number(
                this.tarefa.anexos_count ??
                    this.tarefa.anexosCount ??
                    (this.tarefa.anexos && this.tarefa.anexos.length) ??
                    0
            )
        },
        temMeta() {
            return (
                !!this.tarefa.datahora_entrega ||
                !!this.progress.total ||
                !!this.anexosCount ||
                !!this.tarefa.bloqueios_count ||
                !!this.tarefa.comentarios_count ||
                !!this.tarefa.concluido
            )
        }
    },
    methods: {
        inicialNome,
        formatData(value) {
            if (!value) return ''
            if (typeof value === 'string' && value.includes('/')) {
                return value.replace(/\sàs\s.*/, '')
            }
            try {
                const d = new Date(value)
                if (Number.isNaN(d.getTime())) return String(value)
                return d.toLocaleDateString('pt-BR')
            } catch (e) {
                return String(value)
            }
        }
    }
}
</script>
