<template>
    <div class="wr-kanban" :style="{ background: boardBg }">
        <div class="wr-kanban__topbar">
            <button type="button" class="wr-kanban__chip wr-kanban__chip--primary" @click="$emit('back')">
                <i class="fas fa-th-large"></i>
                <span>Quadros</span>
            </button>

            <div class="wr-kanban__title-area">
                <h4
                    v-if="!editingTitle"
                    class="wr-kanban__title mb-0"
                    :title="canRenameQuadro ? 'Clique para renomear' : ''"
                    @click="canRenameQuadro ? startEditTitle() : null"
                >
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

            <div class="wr-kanban__members">
                <span
                    v-for="m in membrosPreview"
                    :key="m.id"
                    class="wr-kanban__avatar wr-tip"
                    :data-tip="m.nome + (m.papel === 'dono' ? ' (Dono)' : '')"
                    :aria-label="m.nome + (m.papel === 'dono' ? ' (Dono)' : '')"
                    tabindex="0"
                >
                    {{ inicial(m.nome) }}
                </span>
                <span
                    v-if="membrosExtra > 0"
                    class="wr-kanban__avatar wr-kanban__avatar--more wr-tip"
                    :data-tip="membrosExtra + ' membro(s)'"
                    :aria-label="membrosExtra + ' membro(s)'"
                    tabindex="0"
                >
                    +{{ membrosExtra }}
                </span>
                <button type="button" class="wr-kanban__chip wr-kanban__chip--primary" @click="shareOpen = true">
                    <i class="fas fa-user-plus"></i>
                    <span>Compartilhar</span>
                </button>
            </div>
        </div>

        <BoardShareModal
            :open="shareOpen"
            :empresa-id="empresaId"
            :quadro-id="quadro.id"
            :sou-dono="!!quadro.sou_dono"
            @close="shareOpen = false"
            @changed="$emit('members-changed')"
        />

        <div
            class="wr-kanban__board"
            :class="{ 'wr-kanban__board--empty': !listasLocal.length }"
        >
            <draggable
                v-show="listasLocal.length"
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
                            </h5>
                            <span class="wr-lista__count">{{ (lista.tarefas || []).length }}</span>

                            <div
                                v-if="canUpdateLista || canDeleteLista"
                                class="wr-lista__menu"
                                @click.stop
                                @mousedown.stop
                                @pointerdown.stop
                            >
                                <button
                                    type="button"
                                    class="wr-lista__menu-btn"
                                    title="Ações da lista"
                                    aria-label="Ações da lista"
                                    :aria-expanded="listaMenuId === lista.id"
                                    @click="toggleListaMenu(lista.id, $event)"
                                >
                                    <i class="fas fa-ellipsis-h"></i>
                                </button>
                                <div
                                    v-if="listaMenuId === lista.id"
                                    class="wr-lista__menu-panel"
                                    role="menu"
                                    :style="listaMenuStyle"
                                >
                                    <button
                                        v-if="canUpdateLista"
                                        type="button"
                                        class="wr-lista__menu-item"
                                        role="menuitem"
                                        @click="onListaMenuRename(lista)"
                                    >
                                        <i class="fas fa-pen"></i> Renomear
                                    </button>
                                    <button
                                        v-if="canDeleteLista"
                                        type="button"
                                        class="wr-lista__menu-item wr-lista__menu-item--danger"
                                        role="menuitem"
                                        @click="onListaMenuDelete(lista)"
                                    >
                                        <i class="fas fa-trash"></i> Excluir lista
                                    </button>
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

                        <footer v-if="canInsertTarefa" class="wr-lista__footer">
                            <form v-if="addingListaId === lista.id" class="wr-lista__composer" @submit.prevent="criarTarefa(lista)">
                                <textarea
                                    ref="novaTarefaInput"
                                    v-model="novaTarefaTitulo"
                                    class="form-control form-control-sm mb-2"
                                    rows="2"
                                    placeholder="Digite um título para este card…"
                                    @keydown.enter.exact.prevent="criarTarefa(lista)"
                                    @keydown.esc.prevent="cancelAdd"
                                ></textarea>
                                <button class="btn btn-sm btn-success mr-1" type="submit" :disabled="!novaTarefaTitulo.trim() || savingTarefa">
                                    <i v-if="savingTarefa" class="fa fa-spinner fa-pulse"></i>
                                    Adicionar card
                                </button>
                                <button class="btn btn-sm btn-link text-muted p-0 px-1" type="button" @click="cancelAdd">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            <button
                                v-else
                                type="button"
                                class="wr-lista__add-card"
                                @click="startAdd(lista)"
                            >
                                <i class="fas fa-plus"></i> Adicionar um card
                            </button>
                        </footer>
                    </section>
                </template>
            </draggable>

            <section v-if="canInsertLista" class="wr-lista wr-lista--add">
                <form v-if="addingLista" class="wr-lista__composer wr-lista__composer--add-list" @submit.prevent="criarLista">
                    <input
                        ref="novaListaInput"
                        v-model="novaListaTitulo"
                        class="form-control form-control-sm mb-2"
                        placeholder="Insira o título da lista…"
                        @keydown.esc.prevent="addingLista = false"
                    />
                    <div class="wr-lista__composer-actions">
                        <button
                            class="btn btn-sm btn-outline-primary"
                            type="submit"
                            :disabled="!novaListaTitulo.trim() || savingLista"
                        >
                            <i v-if="savingLista" class="fa fa-spinner fa-pulse"></i>
                            <template v-else>Adicionar lista</template>
                        </button>
                        <button
                            class="btn btn-sm btn-link text-danger p-0 px-1"
                            type="button"
                            title="Cancelar"
                            aria-label="Cancelar"
                            @click="addingLista = false"
                        >
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </form>
                <button
                    v-else
                    type="button"
                    class="wr-kanban__chip wr-kanban__chip--primary wr-kanban__chip--block"
                    @click="startAddLista"
                >
                    <i class="fas fa-plus"></i>
                    <span>Adicionar outra lista</span>
                </button>
            </section>
        </div>
    </div>
</template>

<script>
import draggable from 'vuedraggable'
import TaskCard from './TaskCard.vue'
import BoardShareModal from './BoardShareModal.vue'
import { inicialNome, quadroTileBg } from './api'

export default {
    name: 'KanbanBoard',
    components: { draggable, TaskCard, BoardShareModal },
    props: {
        quadro: { type: Object, required: true },
        empresaId: { type: [Number, String], required: true },
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
        'open-tarefa',
        'members-changed'
    ],
    data() {
        return {
            editingTitle: false,
            tituloLocal: this.quadro.titulo,
            editingListaId: null,
            listaMenuId: null,
            listaMenuStyle: null,
            addingLista: false,
            novaListaTitulo: '',
            addingListaId: null,
            novaTarefaTitulo: '',
            tarefaMovida: null,
            draggingTarefa: false,
            dragBlockedClick: false,
            savingLista: false,
            savingTarefa: false,
            listasLocal: [],
            shareOpen: false
        }
    },
    mounted() {
        this._onDocClickListaMenu = (e) => {
            if (!this.listaMenuId) return
            if (!e.target?.closest?.('.wr-lista__menu')) {
                this.closeListaMenu()
            }
        }
        this._onEscListaMenu = (e) => {
            if (e.key === 'Escape') this.closeListaMenu()
        }
        this._onScrollListaMenu = () => {
            if (this.listaMenuId) this.closeListaMenu()
        }
        document.addEventListener('click', this._onDocClickListaMenu)
        document.addEventListener('keydown', this._onEscListaMenu)
        window.addEventListener('scroll', this._onScrollListaMenu, true)
    },
    beforeUnmount() {
        document.removeEventListener('click', this._onDocClickListaMenu)
        document.removeEventListener('keydown', this._onEscListaMenu)
        window.removeEventListener('scroll', this._onScrollListaMenu, true)
    },
    computed: {
        boardBg() {
            return quadroTileBg(this.quadro?.id)
        },
        canRenameQuadro() {
            return !!(this.canUpdateQuadro && this.quadro?.sou_dono)
        },
        membrosPreview() {
            const list = this.quadro?.membros_preview || []
            return list.slice(0, 5)
        },
        membrosExtra() {
            const total = Number(this.quadro?.membros_count || 0)
            return Math.max(0, total - this.membrosPreview.length)
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
        inicial(nome) {
            return inicialNome(nome)
        },
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
        closeListaMenu() {
            this.listaMenuId = null
            this.listaMenuStyle = null
        },
        toggleListaMenu(listaId, event) {
            if (this.listaMenuId === listaId) {
                this.closeListaMenu()
                return
            }
            const btn = event?.currentTarget
            if (btn?.getBoundingClientRect) {
                const r = btn.getBoundingClientRect()
                const width = 168
                this.listaMenuStyle = {
                    position: 'fixed',
                    top: `${Math.round(r.bottom + 4)}px`,
                    left: `${Math.round(Math.min(window.innerWidth - width - 8, Math.max(8, r.right - width)))}px`,
                    zIndex: 1060
                }
            } else {
                this.listaMenuStyle = null
            }
            this.listaMenuId = listaId
        },
        onListaMenuRename(lista) {
            this.closeListaMenu()
            this.startEditLista(lista)
        },
        onListaMenuDelete(lista) {
            this.closeListaMenu()
            this.$emit('delete-lista', lista)
        },
        startEditLista(lista) {
            this.closeListaMenu()
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
            const titulo = String(this.novaListaTitulo || '').replace(/\s+/g, ' ').trim()
            if (!titulo || this.savingLista) return
            this.savingLista = true
            this.$emit('create-lista', titulo)
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
            const titulo = String(this.novaTarefaTitulo || '').replace(/\s+/g, ' ').trim()
            if (!titulo || this.savingTarefa) return
            this.savingTarefa = true
            this.$emit('create-tarefa', lista, titulo)
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
