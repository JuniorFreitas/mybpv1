<template>
    <fieldset class="mybp-filtros-fieldset">
        <legend>{{ titulo }}</legend>
        <form @submit.prevent="emitSubmit" @keydown.enter="onCampoBuscaEnter">
            <div class="row align-items-end mybp-filtros-form">
                <slot name="filtros" />
            </div>

            <div v-if="$slots.acoes || mostrarLimparFiltros" class="row mybp-filtros-acoes-row">
                <div class="col-12">
                    <div class="mybp-filtros-botoes">
                        <slot name="acoes" />
                        <button
                            v-if="mostrarLimparFiltros"
                            type="button"
                            class="btn btn-sm btn-outline-secondary"
                            :disabled="desabilitado"
                            @click="emitLimpar"
                        >
                            <i class="fa fa-times"></i> Limpar filtros
                        </button>
                    </div>
                </div>
            </div>

            <button type="submit" class="sr-only" tabindex="-1" aria-hidden="true">Aplicar filtros</button>
        </form>
    </fieldset>
</template>

<script setup>
defineProps({
    titulo: {
        type: String,
        default: 'Filtro'
    },
    mostrarLimparFiltros: {
        type: Boolean,
        default: false
    },
    desabilitado: {
        type: Boolean,
        default: false
    }
})

const emit = defineEmits(['submit', 'limpar'])

function emitSubmit() {
    emit('submit')
}

function emitLimpar() {
    emit('limpar')
}

function onCampoBuscaEnter(event) {
    if (event.target?.closest?.('.combobox-ac-wrap, .mybp-combobox-wrap, .ma-filtro-combo')) {
        return
    }

    const el = event.target

    if (el?.tagName !== 'INPUT' && el?.tagName !== 'TEXTAREA') {
        return
    }

    const type = (el.type || 'text').toLowerCase()

    if (type !== 'text' && type !== 'search') {
        return
    }

    event.preventDefault()
    emitSubmit()
}
</script>
