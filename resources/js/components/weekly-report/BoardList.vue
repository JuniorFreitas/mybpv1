<template>
    <div class="wr-boards">
        <div class="mybp-projeto-linha mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
                <div>
                    <h5 class="mb-1">Seus quadros</h5>
                    <p class="text-muted small mb-0">Organize demandas em listas e cards no formato kanban</p>
                </div>
                <span v-if="lista.length" class="badge badge-primary badge-pill align-self-start">
                    {{ lista.length }} {{ lista.length === 1 ? 'quadro' : 'quadros' }}
                </span>
            </div>

            <form v-if="canInsert" class="form-row align-items-end" @submit.prevent="criar">
                <div class="form-group col-md-9 mb-2 mb-md-0">
                    <label class="mybp-label">Novo quadro <span class="text-danger">*</span></label>
                    <input
                        v-model.trim="novoTitulo"
                        class="form-control form-control-sm"
                        type="text"
                        maxlength="120"
                        placeholder="Ex.: Onboarding, Recrutamento, Projetos RH..."
                        :disabled="saving"
                    />
                </div>
                <div class="form-group col-md-3 mb-0">
                    <button class="btn btn-sm btn-primary btn-block" type="submit" :disabled="!novoTitulo || saving">
                        <i v-if="saving" class="fa fa-spinner fa-pulse"></i>
                        <template v-else>
                            <i class="fas fa-plus"></i> Criar
                        </template>
                    </button>
                </div>
            </form>
            <div v-else class="alert alert-light border small mb-0 mt-2">
                Seu perfil não tem permissão para criar quadros. Peça ao administrador as habilidades de Weekly Report.
            </div>
        </div>

        <div v-if="preload" class="text-center text-muted py-5">
            <i class="fa fa-spinner fa-pulse fa-2x mb-2 d-block"></i>
            Carregando quadros...
        </div>

        <div v-else-if="!lista.length" class="alert alert-light border text-center py-5 mb-0">
            <i class="fas fa-clipboard-list fa-2x text-muted mb-3 d-block"></i>
            <strong>Nenhum quadro ainda</strong>
            <p class="text-muted mb-0 mt-1">Crie o primeiro quadro para começar a organizar as tarefas.</p>
        </div>

        <div v-else class="mybp-cards-lista">
            <BoardCard
                v-for="quadro in lista"
                :key="quadro.id"
                :quadro="quadro"
                :can-update="canUpdate"
                :can-delete="canDelete"
                @open="$emit('open', quadro)"
                @rename="$emit('rename', quadro)"
                @delete="$emit('delete', quadro)"
            />
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
        return { novoTitulo: '' }
    },
    methods: {
        criar() {
            if (!this.novoTitulo) return
            this.$emit('create', this.novoTitulo)
            this.novoTitulo = ''
        }
    }
}
</script>
