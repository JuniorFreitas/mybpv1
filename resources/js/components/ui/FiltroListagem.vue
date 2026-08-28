<template>
    <fieldset class="mybp-filtros-fieldset">
        <legend>{{ titulo }}</legend>
        <form @submit.prevent="$emit('submit')">
            <div class="row align-items-end mybp-filtros-form">
                <slot name="filtros" />
            </div>

            <div v-if="$slots.acoes" class="row mybp-filtros-acoes-row">
                <div class="col-12">
                    <div class="mybp-filtros-botoes">
                        <slot name="acoes" />
                    </div>
                </div>
            </div>
        </form>
    </fieldset>
</template>

<script setup>
defineProps({
    titulo: {
        type: String,
        default: 'Filtro'
    }
})

defineEmits(['submit'])
</script>
