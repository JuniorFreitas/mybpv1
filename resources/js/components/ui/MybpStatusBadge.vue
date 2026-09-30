<template>
    <span class="mybp-status-badge" :class="`mybp-status-badge--${variante}`">
        <slot>{{ texto }}</slot>
    </span>
</template>

<script>
export default {
    name: 'MybpStatusBadge',
    props: {
        texto: { type: String, default: '' },
        variante: {
            type: String,
            default: 'aberto',
            validator: (v) =>
                ['aberto', 'pendente', 'gestor', 'extra', 'rh', 'aprovado', 'reprovado', 'neutro'].includes(v)
        }
    }
}
</script>
