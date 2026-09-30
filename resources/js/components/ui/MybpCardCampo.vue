<template>
    <div class="mybp-card-campo" :class="{ 'mybp-card-campo--full': full }">
        <span class="mybp-card-campo__label">
            <i v-if="icon" :class="icon" aria-hidden="true"></i>
            {{ label }}
        </span>
        <span class="mybp-card-campo__valor" :class="valorClasses">
            <slot>{{ textoExibido }}</slot>
        </span>
        <span v-if="meta" class="mybp-card-campo__meta">{{ meta }}</span>
    </div>
</template>

<script>
export default {
    name: 'MybpCardCampo',
    props: {
        label: { type: String, required: true },
        valor: { type: [String, Number], default: null },
        icon: { type: String, default: '' },
        meta: { type: String, default: '' },
        fallback: { type: String, default: 'Não informado' },
        forte: { type: Boolean, default: false },
        full: { type: Boolean, default: false },
        linhas: { type: Boolean, default: false },
        tom: {
            type: String,
            default: '',
            validator: (v) => ['', 'positivo', 'negativo', 'meta'].includes(v)
        }
    },
    computed: {
        textoExibido() {
            const v = this.valor
            if (v === null || v === undefined || String(v).trim() === '') {
                return this.fallback
            }
            return v
        },
        valorClasses() {
            return {
                'mybp-card-campo__valor--forte': this.forte,
                'mybp-card-campo__valor--linhas': this.linhas,
                'mybp-card-campo__valor--positivo': this.tom === 'positivo',
                'mybp-card-campo__valor--negativo': this.tom === 'negativo',
                'mybp-card-campo__valor--meta': this.tom === 'meta'
            }
        }
    }
}
</script>
