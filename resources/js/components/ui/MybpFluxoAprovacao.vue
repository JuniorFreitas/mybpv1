<template>
    <div class="mybp-fluxo">
        <div class="mybp-fluxo__icons">
            <template v-for="(step, index) in stepsVisiveis" :key="stepKey(step, index)">
                <i
                    v-if="index > 0"
                    class="fas fa-chevron-right mybp-fluxo__seta mx-2"
                    aria-hidden="true"
                ></i>
                <div class="mybp-fluxo__step">
                    <i :class="iconeStatus(step.status)" aria-hidden="true"></i>
                    <div class="mybp-fluxo__info">
                        <small class="mybp-fluxo__etapa">{{ step.label }}</small>
                        <small
                            v-if="mostrarNome(step)"
                            class="mybp-fluxo__aprovador"
                            :class="classeNome(step.status)"
                        >{{ step.nome }}</small>
                        <small
                            v-else
                            class="mybp-fluxo__status"
                            :class="classeStatusTexto(step.status)"
                        >{{ textoStatus(step) }}</small>
                        <small v-if="step.data" class="mybp-fluxo__data">{{ step.data }}</small>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script>
const STATUS_VALIDOS = ['aprovado', 'reprovado', 'aguardando', 'pendente', 'cancelado']

export default {
    name: 'MybpFluxoAprovacao',
    props: {
        /**
         * Etapas do fluxo.
         * { label, status, nome?, data?, statusTexto?, oculto? }
         * status: aprovado | reprovado | aguardando | pendente | cancelado
         */
        steps: {
            type: Array,
            default: () => []
        }
    },
    computed: {
        stepsVisiveis() {
            return (this.steps || []).filter((s) => s && !s.oculto)
        }
    },
    methods: {
        stepKey(step, index) {
            return step.key || `${step.label || 'step'}-${index}`
        },
        normalizarStatus(status) {
            const s = String(status || 'pendente').toLowerCase()
            return STATUS_VALIDOS.includes(s) ? s : 'pendente'
        },
        iconeStatus(status) {
            switch (this.normalizarStatus(status)) {
                case 'aprovado':
                    return 'fas fa-check-circle text-success'
                case 'reprovado':
                    return 'fas fa-times-circle text-danger'
                case 'aguardando':
                    return 'fas fa-clock text-warning'
                case 'cancelado':
                    return 'fas fa-ban text-secondary'
                default:
                    return 'fas fa-circle text-muted'
            }
        },
        mostrarNome(step) {
            const status = this.normalizarStatus(step.status)
            return (
                (status === 'aprovado' || status === 'reprovado') &&
                step.nome &&
                String(step.nome).trim() !== ''
            )
        },
        classeNome(status) {
            const s = this.normalizarStatus(status)
            if (s === 'aprovado') return 'text-success'
            if (s === 'reprovado') return 'text-danger'
            return ''
        },
        classeStatusTexto(status) {
            const s = this.normalizarStatus(status)
            if (s === 'aguardando') return 'text-warning'
            if (s === 'cancelado') return 'text-secondary'
            return ''
        },
        textoStatus(step) {
            if (step.statusTexto) return step.statusTexto
            switch (this.normalizarStatus(step.status)) {
                case 'aguardando':
                    return 'Aguardando'
                case 'cancelado':
                    return 'Cancelada por reprovação'
                case 'aprovado':
                    return 'Aprovado'
                case 'reprovado':
                    return 'Reprovado'
                default:
                    return 'Pendente'
            }
        }
    }
}
</script>
