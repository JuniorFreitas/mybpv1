<template>
    <div class="wr-kanban">
        <div class="wr-kanban__topbar">
            <button type="button" class="btn btn-sm btn-outline-primary" @click="$emit('back')">
                <i class="fas fa-arrow-left"></i>
                Quadros
            </button>

            <div class="wr-kanban__title-area">
                <h4
                    v-if="!editingTitle"
                    class="wr-kanban__title mb-0"
                    :title="canUpdateQuadro ? 'Clique para renomear' : ''"
                    @click="canUpdateQuadro ? startEditTitle() : null"
                >
                    <i class="fas fa-columns mr-2 text-primary"></i>
                    {{ quadro.titulo }}
                </h4>
                <input
                    v-else
                    ref="tituloInput"
                    v-model="tituloLocal"
                    class="form-control form-control-sm wr-kanban__title-input"
                    @blur="salvarTitulo"
                    @keydown.enter.prevent="salvarTitulo"
                    @keydown.esc.prevent="cancelEditTitle"
                />
            </div>

            <div v-if="atividades && atividades.length" class="wr-kanban__activity d-none d-lg-flex">
                <small
                    v-for="log in atividades.slice(0, 2)"
                    :key="log.id"
                    class="wr-activity-item"
                    :title="(log.usuario?.nome || 'Alguém') + ' ' + log.descricao"
                >
                    <i class="fas fa-history mr-1"></i>
                    {{ log.usuario?.nome || 'Alguém' }} {{ log.descricao }}
                </small>
            </div>
        </div>

        <div class="wr-kanban__board">
            <draggable
                :list="listasLocal"
                item-key="id"
                class="wr-kanban__columns"
                group="listas"
                handle=".wr-lista-handle"
                ghost-class="placeholder"
                animation="180"
                :disabled="!canUpdateLista"
                @change="onListaChange"
            >
                <template #item="{ element: lista }">
                    <section class="wr-lista">
                        <header class="wr-lista__header">
                            <span v-if="canUpdateLista" class="wr-lista-handle text-muted" title="Arrastar lista">
                                <i class="fas fa-grip-vertical"></i>
                            </span>

                            <input
                                v-if="editingListaId === lista.id"
                                :ref="'lista_' + lista.id"
                                v-model="lista.titulo"
                                class="form-control form-control-sm flex-grow-1"
                                @blur="salvarLista(lista)"
                                @keydown.enter.prevent="salvarLista(lista)"
                                @keydown.esc.prevent="editingListaId = null"
                            />
                            <h5
                                v-else
                                class="wr-lista__title mb-0"
                                @click="canUpdateLista ? startEditLista(lista) : null"
                            >
                                {{ lista.titulo }}
                                <span class="badge badge-light border ml-1">{{ (lista.tarefas || []).length }}</span>
                            </h5>

                            <div v-if="canUpdateLista || canDeleteLista" class="dropdown ml-auto">
                                <button
                                    class="btn btn-sm btn-link text-muted p-0 px-1"
                                    type="button"
                                    data-toggle="dropdown"
                                >
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a
                                        v-if="canUpdateLista"
                                        class="dropdown-item"
                                        href="#"
                                        @click.prevent="startEditLista(lista)"
                                    >
                                        Renomear
                                    </a>
                                    <a
                                        v-if="canDeleteLista"
                                        class="dropdown-item text-danger"
                                        href="#"
                                        @click.prevent="$emit('delete-lista', lista)"
                                    >
                                        Excluir lista
                                    </a>
                                </div>
                            </div>
                        </header>

                        <draggable
                            :list="lista.tarefas"
                            item-key="id"
                            class="wr-lista__cards"
                            group="tarefas"
                            ghost-class="placeholder"
                            animation="150"
                            :delay="120"
                            :delay-on-touch-only="true"
                            :disabled="!canUpdateTarefa"
                            @start="draggingTarefa = true"
                            @end="onDragEnd"
                            @change="(e) => onTarefaChange(e, lista)"
                        >
                            <template #item="{ element: tarefa }">
                                <div class="mb-2 wr-task-wrap">
                                    <TaskCard
                                        :tarefa="tarefa"
                                        :lista="lista"
                                        @open="onOpenTarefa"
                                    />
                                </div>
                            </template>
                        </draggable>

                        <div v-if="!(lista.tarefas || []).length && addingListaId !== lista.id" class="wr-lista__empty">
                            Arraste um card ou adicione abaixo
                        </div>

                        <footer v-if="canInsertTarefa" class="wr-lista__footer">
                            <form v-if="addingListaId === lista.id" @submit.prevent="criarTarefa(lista)">
                                <textarea
                                    ref="novaTarefaInput"
                                    v-model.trim="novaTarefaTitulo"
                                    class="form-control form-control-sm mb-2"
                                    rows="2"
                                    placeholder="Título do card"
                                    @keydown.enter.exact.prevent="criarTarefa(lista)"
                                    @keydown.esc.prevent="cancelAdd"
                                ></textarea>
                                <button class="btn btn-sm btn-primary mr-1" type="submit" :disabled="!novaTarefaTitulo || savingTarefa">
                                    <i v-if="savingTarefa" class="fa fa-spinner fa-pulse"></i>
                                    Adicionar
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" type="button" @click="cancelAdd">
                                    Cancelar
                                </button>
                            </form>
                            <button
                                v-else
                                type="button"
                                class="btn btn-sm btn-outline-primary btn-block"
                                @click="startAdd(lista)"
                            >
                                <i class="fas fa-plus"></i> Adicionar card
                            </button>
                        </footer>
                    </section>
                </template>
            </draggable>

            <section v-if="canInsertLista" class="wr-lista wr-lista--add">
                <form v-if="addingLista" class="p-2" @submit.prevent="criarLista">
                    <input
                        ref="novaListaInput"
                        v-model.trim="novaListaTitulo"
                        class="form-control form-control-sm mb-2"
                        placeholder="Nome da lista"
                        @keydown.esc.prevent="addingLista = false"
                    />
                    <button class="btn btn-sm btn-primary mr-1" type="submit" :disabled="!novaListaTitulo || savingLista">
                        <i v-if="savingLista" class="fa fa-spinner fa-pulse"></i>
                        Adicionar lista
                    </button>
                    <button class="btn btn-sm btn-outline-secondary" type="button" @click="addingLista = false">
                        Cancelar
                    </button>
                </form>
                <button v-else type="button" class="btn btn-sm btn-light btn-block wr-lista__add-btn" @click="startAddLista">
                    <i class="fas fa-plus"></i> Adicionar lista
                </button>
            </section>
        </div>
    </div>
</template>

<script>
import draggable from 'vuedraggable'
import TaskCard from './TaskCard.vue'

export default {
    name: 'KanbanBoard',
    components: { draggable, TaskCard },
    props: {
        quadro: { type: Object, required: true },
        listas: { type: Array, default: () => [] },
        atividades: { type: Array, default: () => [] },
        canUpdateQuadro: { type: Boolean, default: false },
        canInsertLista: { type: Boolean, default: false },
        canUpdateLista: { type: Boolean, default: false },
        canDeleteLista: { type: Boolean, default: false },
        canInsertTarefa: { type: Boolean, default: false },
        canUpdateTarefa: { type: Boolean, default: false }
    },
    emits: [
        'back',
        'update:listas',
        'rename-quadro',
        'create-lista',
        'rename-lista',
        'delete-lista',
        'reorder-listas',
        'create-tarefa',
        'reorder-tarefas',
        'open-tarefa'
    ],
    data() {
        return {
            editingTitle: false,
            tituloLocal: this.quadro.titulo,
            editingListaId: null,
            addingLista: false,
            novaListaTitulo: '',
            addingListaId: null,
            novaTarefaTitulo: '',
            tarefaMovida: null,
            draggingTarefa: false,
            dragBlockedClick: false,
            savingLista: false,
            savingTarefa: false,
            listasLocal: []
        }
    },
    watch: {
        listas: {
            immediate: true,
            handler(v) {
                // Cópia local mutável para o drag (:list). Sem deep — só quando o pai troca a referência (reload).
                this.listasLocal = (v || []).map((l) => ({
                    ...l,
                    tarefas: Array.isArray(l.tarefas) ? l.tarefas.slice() : []
                }))
            }
        },
        'quadro.titulo'(v) {
            this.tituloLocal = v
        }
    },
    methods: {
        startEditTitle() {
            this.tituloLocal = this.quadro.titulo
            this.editingTitle = true
            this.$nextTick(() => this.$refs.tituloInput && this.$refs.tituloInput.focus())
        },
        cancelEditTitle() {
            this.editingTitle = false
            this.tituloLocal = this.quadro.titulo
        },
        salvarTitulo() {
            this.editingTitle = false
            if (this.tituloLocal && this.tituloLocal.trim() && this.tituloLocal !== this.quadro.titulo) {
                this.$emit('rename-quadro', this.tituloLocal.trim())
            } else {
                this.tituloLocal = this.quadro.titulo
            }
        },
        startEditLista(lista) {
            this.editingListaId = lista.id
            this.$nextTick(() => {
                const ref = this.$refs['lista_' + lista.id]
                const el = Array.isArray(ref) ? ref[0] : ref
                if (el) el.focus()
            })
        },
        salvarLista(lista) {
            this.editingListaId = null
            if (lista.titulo && lista.titulo.trim()) {
                lista.titulo = lista.titulo.trim()
                this.$emit('rename-lista', lista)
            }
        },
        startAddLista() {
            this.addingLista = true
            this.novaListaTitulo = ''
            this.$nextTick(() => this.$refs.novaListaInput && this.$refs.novaListaInput.focus())
        },
        criarLista() {
            if (!this.novaListaTitulo || this.savingLista) return
            this.savingLista = true
            this.$emit('create-lista', this.novaListaTitulo)
            this.novaListaTitulo = ''
            this.addingLista = false
            this.savingLista = false
        },
        startAdd(lista) {
            this.addingListaId = lista.id
            this.novaTarefaTitulo = ''
            this.$nextTick(() => {
                const el = this.$refs.novaTarefaInput
                if (el) el.focus()
            })
        },
        cancelAdd() {
            this.addingListaId = null
            this.novaTarefaTitulo = ''
        },
        criarTarefa(lista) {
            if (!this.novaTarefaTitulo || this.savingTarefa) return
            this.savingTarefa = true
            this.$emit('create-tarefa', lista, this.novaTarefaTitulo)
            this.cancelAdd()
            this.savingTarefa = false
        },
        onDragEnd() {
            this.dragBlockedClick = this.draggingTarefa
            this.draggingTarefa = false
            setTimeout(() => {
                this.dragBlockedClick = false
            }, 50)
        },
        onOpenTarefa(tarefa, lista) {
            if (this.draggingTarefa || this.dragBlockedClick) return
            this.$emit('open-tarefa', tarefa, lista)
        },
        onListaChange() {
            const ordem = this.listasLocal.map((l, i) => ({ id: l.id, ordem: i + 1 }))
            this.$emit('reorder-listas', ordem)
        },
        onTarefaChange(event, lista) {
            if (!lista.tarefas) lista.tarefas = []

            if (event.moved) {
                lista.tarefas.forEach((t, i) => {
                    t.ordem = i + 1
                    t.lista_id = lista.id
                })
                this.$emit('reorder-tarefas', {
                    evento: 'moveu',
                    lista,
                    novaLista: lista.tarefas.map((t) => ({
                        id: t.id,
                        lista_id: lista.id,
                        ordem: t.ordem
                    }))
                })
            }

            if (event.added) {
                const tarefa = event.added.element
                this.tarefaMovida = { id: tarefa.id, lista_id: tarefa.lista_id }
                lista.tarefas.forEach((t, i) => {
                    t.ordem = i + 1
                    t.lista_id = lista.id
                })
                this.$emit('reorder-tarefas', {
                    evento: 'adicionar',
                    lista,
                    tarefa_id: tarefa.id,
                    novaLista: lista.tarefas.map((t) => ({
                        id: t.id,
                        lista_id: lista.id,
                        ordem: t.ordem
                    }))
                })
            }

            if (event.removed) {
                // lista já está sem o item (mutação :list)
                lista.tarefas.forEach((t, i) => {
                    t.ordem = i + 1
                    t.lista_id = lista.id
                })
                this.$emit('reorder-tarefas', {
                    evento: 'remover',
                    lista,
                    novaLista: lista.tarefas.map((t) => ({
                        id: t.id,
                        lista_id: lista.id,
                        ordem: t.ordem
                    }))
                })
            }
        }
    }
}
</script>
