<template>
    <div class="wr-boards">
        <header class="wr-boards__header">
            <div class="wr-boards__heading">
                <i class="fas fa-user wr-boards__heading-icon" aria-hidden="true"></i>
                <h5 class="wr-boards__title mb-0">Seus quadros</h5>
            </div>
            <span v-if="lista.length" class="wr-boards__count">
                {{ lista.length }} {{ lista.length === 1 ? 'quadro' : 'quadros' }}
            </span>
        </header>

        <div v-if="preload" class="wr-boards__loading text-muted">
            <i class="fa fa-spinner fa-pulse fa-2x mb-2 d-block"></i>
            Carregando quadros...
        </div>

        <div v-else class="wr-boards__grid">
            <BoardCard
                v-for="quadro in lista"
                :key="quadro.id"
                :quadro="quadro"
                :can-update="canUpdate && !!quadro.sou_dono"
                :can-delete="canDelete && !!quadro.sou_dono"
                :saving="saving"
                @open="$emit('open', quadro)"
                @rename="$emit('rename', $event)"
                @delete="$emit('delete', quadro)"
            />

            <div
                v-if="canInsert"
                class="wr-board-tile wr-board-tile--create"
                :class="{ 'wr-board-tile--create-open': criando }"
            >
                <button
                    v-if="!criando"
                    type="button"
                    class="wr-board-tile__create-trigger"
                    @click="abrirCriar"
                >
                    <i class="fas fa-plus"></i>
                    <span>Criar novo quadro</span>
                </button>

                <form v-else class="wr-board-tile__create-form" @submit.prevent="criar">
                    <label class="sr-only" for="wr-novo-quadro">Título do quadro</label>
                    <input
                        id="wr-novo-quadro"
                        ref="novoInput"
                        v-model="novoTitulo"
                        class="form-control form-control-sm wr-board-tile__create-input"
                        type="text"
                        maxlength="120"
                        placeholder="Adicionar título do quadro"
                        :disabled="saving"
                        :aria-invalid="tituloDuplicado ? 'true' : 'false'"
                        @keydown.esc.prevent="fecharCriar"
                    />
                    <small v-if="tituloDuplicado" class="wr-board-tile__create-error">
                        Você já tem ou está neste quadro com este nome.
                    </small>
                    <div class="wr-board-tile__create-actions">
                        <button
                            class="btn btn-sm btn-success"
                            type="submit"
                            :disabled="!tituloValido || saving || tituloDuplicado"
                        >
                            <i v-if="saving" class="fa fa-spinner fa-pulse"></i>
                            <template v-else>Criar quadro</template>
                        </button>
                        <button
                            type="button"
                            class="btn btn-sm btn-link text-muted"
                            :disabled="saving"
                            aria-label="Cancelar"
                            @click="fecharCriar"
                        >
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </form>
            </div>

            <div
                v-else-if="!lista.length"
                class="wr-boards__empty"
            >
                Seu perfil não tem permissão para criar quadros.
            </div>
        </div>

        <div
            v-if="!preload && !lista.length && canInsert && !criando"
            class="wr-boards__hint text-muted"
        >
            Clique em <strong>Criar novo quadro</strong> para começar.
        </div>
    </div>
</template>

<script>
import BoardCard from './BoardCard.vue'

export default {
    name: 'BoardList',
    components: { BoardCard },
    props: {
        lista: { type: Array, default: () => [] },
        preload: { type: Boolean, default: false },
        canInsert: { type: Boolean, default: false },
        canUpdate: { type: Boolean, default: false },
        canDelete: { type: Boolean, default: false },
        saving: { type: Boolean, default: false }
    },
    emits: ['open', 'rename', 'delete', 'create'],
    data() {
        return {
            novoTitulo: '',
            criando: false
        }
    },
    computed: {
        tituloNormalizado() {
            return String(this.novoTitulo || '').replace(/\s+/g, ' ').trim()
        },
        tituloValido() {
            return this.tituloNormalizado.length > 0
        },
        tituloDuplicado() {
            const titulo = this.tituloNormalizado.toLowerCase()
            if (!titulo) return false
            return (this.lista || []).some((q) => String(q.titulo || '').trim().toLowerCase() === titulo)
        }
    },
    watch: {
        saving(v, prev) {
            // Fecha o formulário após criar com sucesso
            if (prev && !v && this.criando && !this.novoTitulo) {
                this.criando = false
            }
        }
    },
    methods: {
        abrirCriar() {
            this.criando = true
            this.$nextTick(() => this.$refs.novoInput && this.$refs.novoInput.focus())
        },
        fecharCriar() {
            this.criando = false
            this.novoTitulo = ''
        },
        criar() {
            if (!this.tituloValido || this.tituloDuplicado) return
            this.$emit('create', this.tituloNormalizado)
            this.novoTitulo = ''
            this.criando = false
        }
    }
}
</script>
