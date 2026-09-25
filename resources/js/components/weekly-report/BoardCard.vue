<template>
    <div class="mybp-card wr-quadro-card" @click="$emit('open', quadro)">
        <div class="mybp-card-header-row border-0 pb-0 mb-0">
            <div class="mybp-card-left">
                <span class="wr-quadro-card__icon">
                    <i class="fas fa-columns"></i>
                </span>
                <div class="min-w-0">
                    <div class="mybp-card-titulo"><strong>{{ quadro.titulo }}</strong></div>
                    <small class="text-muted">Clique para abrir o quadro</small>
                </div>
            </div>
            <div class="mybp-card-right" @click.stop>
                <button
                    v-if="canUpdate"
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    title="Renomear"
                    @click="$emit('rename', quadro)"
                >
                    <i class="fas fa-pen"></i>
                </button>
                <button type="button" class="btn btn-sm btn-primary" @click="$emit('open', quadro)">
                    Abrir
                </button>
                <button
                    v-if="canDelete"
                    type="button"
                    class="btn btn-sm btn-outline-danger"
                    title="Excluir"
                    @click="$emit('delete', quadro)"
                >
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'BoardCard',
    props: {
        quadro: { type: Object, required: true },
        canUpdate: { type: Boolean, default: false },
        canDelete: { type: Boolean, default: false }
    },
    emits: ['open', 'rename', 'delete']
}
</script>
