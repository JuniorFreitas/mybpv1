<template>
    <div class="mybp-card">
        <div class="mybp-card-header-row">
            <div class="mybp-card-left">
                <slot name="selecao"></slot>
                <span class="mybp-badge-id">#{{ entrevista.id }}</span>
                <div class="mybp-card-titulo mybp-card-titulo--stacked">
                    <strong>{{ nome }}</strong>
                    <div v-if="cpf" class="text-muted small mybp-card-subtitulo">CPF {{ cpf }}</div>
                </div>
            </div>
            <div class="mybp-card-right">
                <slot name="status"></slot>
                <div class="dropdown" :class="{ show: aberto }">
                    <a
                        class="mybp-btn-acoes-compact"
                        href="#"
                        role="button"
                        @click.prevent.stop="$emit('toggle')"
                    >
                        <i class="fas fa-ellipsis-v"></i>
                    </a>
                    <div
                        class="dropdown-menu mybp-dropdown-menu dropdown-menu-right"
                        :class="{ show: aberto }"
                        @click="$emit('fechar')"
                    >
                        <slot name="acoes"></slot>
                    </div>
                </div>
            </div>
        </div>
        <div class="mybp-card-corpo">
            <section class="mybp-card-secao">
                <div class="mybp-card-row">
                    <slot name="campos"></slot>
                </div>
            </section>
        </div>
    </div>
</template>

<script>
export default {
    name: 'CartaoEntrevistaLista',
    props: {
        entrevista: { type: Object, required: true },
        aberto: { type: Boolean, default: false }
    },
    emits: ['toggle', 'fechar'],
    computed: {
        nome() {
            return this.entrevista?.curriculo?.nome || 'Não informado'
        },
        cpf() {
            return this.entrevista?.curriculo?.cpf || ''
        }
    }
}
</script>
