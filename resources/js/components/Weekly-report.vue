<template>
    <div class="wr-app">
        <div v-if="loadError" class="alert alert-danger">
            {{ loadError }}
            <button type="button" class="btn btn-sm btn-outline-danger ml-2" @click="carregarQuadros">Tentar novamente</button>
        </div>

        <BoardList
            v-if="!quadroAtivo"
            :lista="listaQuadros"
            :preload="preload"
            :can-insert="perms.quadro_insert"
            :can-update="perms.quadro_update"
            :can-delete="perms.quadro_delete"
            :saving="savingQuadro"
            @open="abrirQuadro"
            @create="criarQuadro"
            @rename="pedirRenameQuadro"
            @delete="pedirDeleteQuadro"
        />

        <div v-else>
            <div v-if="preloadBoard" class="text-center text-muted py-5">
                <i class="fa fa-spinner fa-pulse fa-2x mb-2 d-block"></i>
                Abrindo quadro...
            </div>
            <KanbanBoard
                v-else
                :quadro="quadroAtivo"
                :listas="arrayListas"
                :atividades="atividades"
                :can-update-quadro="perms.quadro_update"
                :can-insert-lista="perms.lista_insert"
                :can-update-lista="perms.lista_update"
                :can-delete-lista="perms.lista_delete"
                :can-insert-tarefa="perms.tarefa_insert"
                :can-update-tarefa="perms.tarefa_update"
                @update:listas="onListasUpdate"
                @back="voltarQuadros"
                @rename-quadro="renomearQuadroAtivo"
                @create-lista="criarLista"
                @rename-lista="renomearLista"
                @delete-lista="pedirDeleteLista"
                @reorder-listas="reorderListas"
                @create-tarefa="criarTarefa"
                @reorder-tarefas="reorderTarefas"
                @open-tarefa="abrirTarefa"
            />
        </div>

        <TaskModal
            v-if="tarefaAtiva && listaAtiva && quadroAtivo"
            ref="taskModal"
            :empresa-id="Number(id)"
            :quadro-id="quadroAtivo.id"
            :lista="listaAtiva"
            :tarefa="tarefaAtiva"
            :user-id="Number(userId)"
            :can-update="perms.tarefa_update"
            :can-delete="perms.tarefa_delete"
            @close="fecharTarefa"
            @delete="pedirDeleteTarefa"
            @updated="onTarefaUpdated"
            @checklist-changed="onChecklistChanged"
            @refresh="reloadBoard"
        />

        <modal id="wrConfirmDelete" titulo="Confirmar exclusão" ref="confirmModal">
            <template #conteudo>
                <div class="alert alert-warning mb-0">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ confirmMsg }}
                </div>
            </template>
            <template #rodape>
                <button type="button" class="btn btn-sm btn-danger" :disabled="confirmBusy" @click="confirmAction">
                    <i v-if="confirmBusy" class="fa fa-spinner fa-pulse"></i>
                    Confirmar
                </button>
            </template>
        </modal>
    </div>
</template>

<script>
import BoardList from './weekly-report/BoardList.vue'
import KanbanBoard from './weekly-report/KanbanBoard.vue'
import TaskModal from './weekly-report/TaskModal.vue'
import { joinWeeklyChannels } from './weekly-report/echo'
import { escreverWeeklyQueryParams, lerWeeklyQueryParams } from './weekly-report/queryParams'
import {
    listasUrl,
    normalizeComentario,
    normalizeListas,
    normalizeLog,
    normalizeTarefa,
    quadroUrl,
    tarefasUrl,
    toastErro,
    toastOk,
    weeklyBase
} from './weekly-report/api'

export default {
    name: 'WeeklyReport',
    components: { BoardList, KanbanBoard, TaskModal },
    props: {
        id: { type: [Number, String], required: true },
        userId: { type: [Number, String], default: null },
        modalPai: { type: String, required: false }
    },
    data() {
        return {
            preload: false,
            preloadBoard: false,
            savingQuadro: false,
            loadError: '',
            listaQuadros: [],
            quadroAtivo: null,
            arrayListas: [],
            atividades: [],
            tarefaAtiva: null,
            listaAtiva: null,
            leaveEcho: null,
            confirmMsg: '',
            confirmFn: null,
            confirmBusy: false,
            perms: {
                quadro_insert: false,
                quadro_update: false,
                quadro_delete: false,
                lista_insert: false,
                lista_update: false,
                lista_delete: false,
                tarefa_insert: false,
                tarefa_update: false,
                tarefa_delete: false
            },
            _skipUrlSync: false,
            _restoringUrl: false
        }
    },
    mounted() {
        this._onPopState = () => this.restaurarDaUrl({ fromPopState: true })
        window.addEventListener('popstate', this._onPopState)

        this.carregarQuadros()
            .then(() => this.restaurarDaUrl({ replace: true }))
            .catch(() => {})

        try {
            this.leaveEcho = joinWeeklyChannels(Number(this.id), {
                onLog: this.onLog,
                onQuadroInsert: (e) => {
                    if (e.quadro && !this.listaQuadros.find((q) => q.id === e.quadro.id)) {
                        this.listaQuadros.push(e.quadro)
                    }
                },
                onQuadroUpdate: (e) => {
                    const q = this.listaQuadros.find((x) => x.id === e.quadro?.id)
                    if (q) Object.assign(q, e.quadro)
                    if (this.quadroAtivo?.id === e.quadro?.id) Object.assign(this.quadroAtivo, e.quadro)
                },
                onQuadroDelete: (e) => {
                    this.listaQuadros = this.listaQuadros.filter((q) => q.id !== e.id)
                    if (this.quadroAtivo?.id === e.id) this.voltarQuadros()
                },
                onListaInsert: (e) => this.mergeListas(e.lista),
                onListaUpdate: (e) => this.mergeListas(e.lista),
                onListaDelete: (e) => {
                    this.mergeListas(e.lista)
                    if (e.idDelete) {
                        this.arrayListas = this.arrayListas.filter((l) => l.id !== e.idDelete)
                    }
                },
                onListaOrdenar: (e) => this.mergeListasOrdem(e.lista),
                onTarefaInsert: (e) => this.applyTarefasLista(e),
                onTarefaUpdate: (e) => this.applyTarefaUpdate(e),
                onTarefaDelete: (e) => this.applyTarefasLista(e),
                onTarefaOrdenar: (e) => this.applyTarefasLista(e),
                onAnexo: (e) => this.applyAnexos(e),
                onChecklist: (e) => this.applyChecklistRealtime(e),
                onChecklistItem: (e) => this.applyChecklistItemRealtime(e),
                onComentario: (e) => this.applyComentarioRealtime(e)
            })
        } catch (e) {
            console.warn('[weekly-report] realtime desabilitado', e)
            this.leaveEcho = () => {}
        }
    },
    beforeUnmount() {
        if (this._onPopState) {
            window.removeEventListener('popstate', this._onPopState)
        }
        if (this.leaveEcho) this.leaveEcho()
    },
    methods: {
        syncUrl({ replace = false } = {}) {
            if (this._skipUrlSync) return
            escreverWeeklyQueryParams(
                {
                    quadro: this.quadroAtivo?.id || null,
                    lista: this.listaAtiva?.id || null,
                    tarefa: this.tarefaAtiva?.id || null
                },
                { replace }
            )
        },
        async restaurarDaUrl({ replace = true, fromPopState = false } = {}) {
            if (this._restoringUrl) return
            this._restoringUrl = true
            this._skipUrlSync = true
            try {
                const { quadro, lista, tarefa } = lerWeeklyQueryParams()

                if (!quadro) {
                    if (this.quadroAtivo) {
                        this.quadroAtivo = null
                        this.arrayListas = []
                        this.atividades = []
                        this.tarefaAtiva = null
                        this.listaAtiva = null
                    }
                    return
                }

                const quadroObj = this.listaQuadros.find((q) => Number(q.id) === Number(quadro))
                if (!quadroObj) {
                    toastErro('Quadro não encontrado ou sem permissão de acesso')
                    escreverWeeklyQueryParams({}, { replace: true })
                    this.quadroAtivo = null
                    this.arrayListas = []
                    this.atividades = []
                    this.tarefaAtiva = null
                    this.listaAtiva = null
                    return
                }

                const mesmoQuadro = this.quadroAtivo && Number(this.quadroAtivo.id) === Number(quadroObj.id)
                if (!mesmoQuadro) {
                    this.quadroAtivo = { ...quadroObj }
                    this.preloadBoard = true
                    this.tarefaAtiva = null
                    this.listaAtiva = null
                    try {
                        await this.reloadBoard()
                    } catch (e) {
                        toastErro(e?.response?.data?.msg || 'Erro ao abrir quadro')
                        this.quadroAtivo = null
                        escreverWeeklyQueryParams({}, { replace: true })
                        return
                    } finally {
                        this.preloadBoard = false
                    }
                } else if (!this.arrayListas.length) {
                    await this.reloadBoard().catch(() => {})
                }

                if (!tarefa) {
                    this.tarefaAtiva = null
                    this.listaAtiva = null
                    return
                }

                let listaObj = lista
                    ? this.arrayListas.find((l) => Number(l.id) === Number(lista))
                    : null
                let tarefaObj = null

                if (listaObj) {
                    tarefaObj = (listaObj.tarefas || []).find((t) => Number(t.id) === Number(tarefa))
                }
                if (!tarefaObj) {
                    for (const l of this.arrayListas) {
                        const found = (l.tarefas || []).find((t) => Number(t.id) === Number(tarefa))
                        if (found) {
                            listaObj = l
                            tarefaObj = found
                            break
                        }
                    }
                }

                if (!tarefaObj && lista) {
                    try {
                        const { data } = await axios.get(tarefasUrl(this.id, quadro, lista, tarefa))
                        tarefaObj = normalizeTarefa(data)
                        listaObj =
                            this.arrayListas.find((l) => Number(l.id) === Number(lista)) || {
                                id: lista,
                                titulo: 'Lista',
                                tarefas: []
                            }
                    } catch (e) {
                        const status = e?.response?.status
                        toastErro(
                            status === 403
                                ? 'Sem permissão para abrir esta tarefa'
                                : e?.response?.data?.msg || 'Tarefa não encontrada'
                        )
                        escreverWeeklyQueryParams({ quadro }, { replace: true })
                        this.tarefaAtiva = null
                        this.listaAtiva = null
                        return
                    }
                }

                if (tarefaObj && listaObj) {
                    this.tarefaAtiva = normalizeTarefa(JSON.parse(JSON.stringify(tarefaObj)))
                    this.listaAtiva = listaObj
                    if (replace || fromPopState) {
                        escreverWeeklyQueryParams(
                            {
                                quadro: quadroObj.id,
                                lista: listaObj.id,
                                tarefa: tarefaObj.id
                            },
                            { replace: true }
                        )
                    }
                } else {
                    toastErro('Tarefa não encontrada neste quadro')
                    escreverWeeklyQueryParams({ quadro: quadroObj.id }, { replace: true })
                    this.tarefaAtiva = null
                    this.listaAtiva = null
                }
            } finally {
                this._skipUrlSync = false
                this._restoringUrl = false
            }
        },
        onListasUpdate(listas) {
            this.arrayListas = listas
        },
        async carregarQuadros() {
            this.preload = true
            this.loadError = ''
            try {
                const { data } = await axios.get(weeklyBase(this.id))
                this.listaQuadros = data.lista || []
                Object.keys(this.perms).forEach((k) => {
                    if (typeof data[k] !== 'undefined') this.perms[k] = !!data[k]
                })
            } catch (e) {
                this.loadError = e?.response?.data?.msg || 'Não foi possível carregar os quadros.'
                throw e
            } finally {
                this.preload = false
            }
        },
        async criarQuadro(titulo) {
            this.savingQuadro = true
            try {
                const { data } = await axios.post(quadroUrl(this.id), { titulo })
                if (data.quadro && !this.listaQuadros.find((q) => q.id === data.quadro.id)) {
                    this.listaQuadros.push(data.quadro)
                }
                toastOk('Quadro criado')
            } catch (e) {
                toastErro(e?.response?.data?.msg || 'Erro ao criar quadro')
            } finally {
                this.savingQuadro = false
            }
        },
        pedirRenameQuadro(quadro) {
            const titulo = window.prompt('Novo nome do quadro', quadro.titulo)
            if (!titulo || !titulo.trim() || titulo === quadro.titulo) return
            axios
                .put(quadroUrl(this.id, quadro.id), { titulo: titulo.trim() })
                .then(({ data }) => {
                    Object.assign(quadro, data.quadro || { titulo: titulo.trim() })
                    toastOk('Quadro atualizado')
                })
                .catch((e) => toastErro(e?.response?.data?.msg || 'Erro ao renomear'))
        },
        pedirDeleteQuadro(quadro) {
            this.confirmMsg = `Excluir o quadro "${quadro.titulo}"? Esta ação não pode ser desfeita.`
            this.confirmFn = async () => {
                this.confirmBusy = true
                try {
                    await axios.delete(quadroUrl(this.id, quadro.id))
                    this.listaQuadros = this.listaQuadros.filter((q) => q.id !== quadro.id)
                    if (this.quadroAtivo?.id === quadro.id) {
                        this.voltarQuadros()
                    }
                    this.$refs.confirmModal.fecharModal()
                    toastOk('Quadro excluído')
                } catch (e) {
                    toastErro(e?.response?.data?.msg || 'Erro ao excluir quadro')
                } finally {
                    this.confirmBusy = false
                }
            }
            this.$nextTick(() => this.$refs.confirmModal && this.$refs.confirmModal.abrirModal())
        },
        async abrirQuadro(quadro, { syncUrl = true } = {}) {
            this.quadroAtivo = { ...quadro }
            this.preloadBoard = true
            this.fecharTarefa({ syncUrl: false })
            try {
                await this.reloadBoard()
                if (syncUrl) this.syncUrl({ replace: false })
            } catch (e) {
                toastErro(e?.response?.data?.msg || 'Erro ao abrir quadro')
                this.quadroAtivo = null
                if (syncUrl) this.syncUrl({ replace: false })
            } finally {
                this.preloadBoard = false
            }
        },
        voltarQuadros({ syncUrl = true } = {}) {
            this.quadroAtivo = null
            this.arrayListas = []
            this.atividades = []
            this.fecharTarefa({ syncUrl: false })
            if (syncUrl) this.syncUrl({ replace: false })
        },
        async reloadBoard() {
            if (!this.quadroAtivo) return
            const { data } = await axios.get(listasUrl(this.id, this.quadroAtivo.id))
            this.arrayListas = normalizeListas(data.lista)
            this.atividades = data.atividades || []
        },
        reloadBoardSoft() {
            if (!this.quadroAtivo || this.preloadBoard) return
            this.reloadBoard().catch(() => {})
        },
        renomearQuadroAtivo(titulo) {
            axios
                .put(quadroUrl(this.id, this.quadroAtivo.id), { titulo })
                .then(({ data }) => {
                    Object.assign(this.quadroAtivo, data.quadro || { titulo })
                    const q = this.listaQuadros.find((x) => x.id === this.quadroAtivo.id)
                    if (q) Object.assign(q, this.quadroAtivo)
                })
                .catch((e) => toastErro(e?.response?.data?.msg || 'Erro ao renomear quadro'))
        },
        async criarLista(titulo) {
            try {
                await axios.post(listasUrl(this.id, this.quadroAtivo.id), { titulo })
                await this.reloadBoard()
                toastOk('Lista criada')
            } catch (e) {
                toastErro(e?.response?.data?.msg || 'Erro ao criar lista')
            }
        },
        renomearLista(lista) {
            axios
                .put(listasUrl(this.id, this.quadroAtivo.id, lista.id), { titulo: lista.titulo })
                .catch((e) => toastErro(e?.response?.data?.msg || 'Erro ao renomear lista'))
        },
        pedirDeleteLista(lista) {
            this.confirmMsg = `Excluir a lista "${lista.titulo}" e todas as tarefas?`
            this.confirmFn = async () => {
                this.confirmBusy = true
                try {
                    await axios.delete(listasUrl(this.id, this.quadroAtivo.id, lista.id))
                    this.arrayListas = this.arrayListas.filter((l) => l.id !== lista.id)
                    if (this.listaAtiva?.id === lista.id) {
                        this.fecharTarefa()
                    }
                    this.$refs.confirmModal.fecharModal()
                    toastOk('Lista excluída')
                } catch (e) {
                    toastErro(e?.response?.data?.msg || 'Erro ao excluir lista')
                } finally {
                    this.confirmBusy = false
                }
            }
            this.$nextTick(() => this.$refs.confirmModal && this.$refs.confirmModal.abrirModal())
        },
        reorderListas(ordem) {
            axios.put(listasUrl(this.id, this.quadroAtivo.id), { novaLista: ordem }).catch(() => {
                this.reloadBoardSoft()
            })
        },
        async criarTarefa(lista, titulo) {
            try {
                await axios.post(tarefasUrl(this.id, this.quadroAtivo.id, lista.id), { titulo })
                await this.reloadBoard()
            } catch (e) {
                toastErro(e?.response?.data?.msg || 'Erro ao criar card')
            }
        },
        reorderTarefas({ evento, lista, novaLista, tarefa_id }) {
            axios
                .put(tarefasUrl(this.id, this.quadroAtivo.id, lista.id), {
                    evento,
                    novaLista,
                    tarefa_id
                })
                .catch(() => this.reloadBoardSoft())
        },
        abrirTarefa(tarefa, lista, { syncUrl = true } = {}) {
            this.tarefaAtiva = normalizeTarefa(JSON.parse(JSON.stringify(tarefa)))
            this.listaAtiva = lista
            if (syncUrl) this.syncUrl({ replace: false })
        },
        fecharTarefa({ syncUrl = true } = {}) {
            this.tarefaAtiva = null
            this.listaAtiva = null
            if (syncUrl) this.syncUrl({ replace: false })
        },
        pedirDeleteTarefa(tarefa) {
            this.confirmMsg = `Excluir o card "${tarefa.titulo}"?`
            this.confirmFn = async () => {
                this.confirmBusy = true
                try {
                    await axios.delete(tarefasUrl(this.id, this.quadroAtivo.id, this.listaAtiva.id, tarefa.id))
                    this.fecharTarefa()
                    await this.reloadBoard()
                    this.$refs.confirmModal.fecharModal()
                    toastOk('Card excluído')
                } catch (e) {
                    toastErro(e?.response?.data?.msg || 'Erro ao excluir card')
                } finally {
                    this.confirmBusy = false
                }
            }
            this.$nextTick(() => this.$refs.confirmModal && this.$refs.confirmModal.abrirModal())
        },
        onTarefaUpdated(tarefa) {
            const norm = normalizeTarefa(tarefa)
            // Atualiza só as listas do quadro — não reatribui tarefaAtiva (evita remount/loop do modal)
            this.arrayListas.forEach((lista) => {
                const t = (lista.tarefas || []).find((x) => Number(x.id) === Number(norm.id))
                if (t) Object.assign(t, norm)
            })
        },
        onChecklistChanged({ acao, checklist_id, tarefa_id, checklists }) {
            if (!tarefa_id) return
            this.patchTarefaChecklists(tarefa_id, { acao, checklist_id, checklists })
        },
        applyChecklistRealtime(e) {
            if (!e?.tarefa_id) return
            if (Array.isArray(e.checklists)) {
                this.patchTarefaChecklists(e.tarefa_id, { checklists: e.checklists })
                return
            }
            if (e.checklist_id) {
                this.patchTarefaChecklists(e.tarefa_id, { acao: 'delete', checklist_id: e.checklist_id })
            }
        },
        applyChecklistItemRealtime(e) {
            // Payload real do ItemChecklistEvent — nunca reloadBoardSoft (era a lentidão)
            if (!e?.tarefa_id || !e?.checklist_id) return

            this.arrayListas.forEach((lista) => {
                const t = (lista.tarefas || []).find((x) => Number(x.id) === Number(e.tarefa_id))
                if (!t) return
                if (!t.checklists) t.checklists = []
                let ck = t.checklists.find((c) => Number(c.id) === Number(e.checklist_id))
                if (!ck) {
                    ck = { id: e.checklist_id, itens: [] }
                    t.checklists.push(ck)
                }
                if (Array.isArray(e.itens)) {
                    ck.itens = e.itens.map((item) => {
                        const membros = item.membros ?? item.Membros
                        return {
                            ...item,
                            membros: Array.isArray(membros) ? membros : []
                        }
                    })
                    return
                }
                if (e.item_id && !e.item) {
                    ck.itens = (ck.itens || []).filter((i) => Number(i.id) !== Number(e.item_id))
                    return
                }
                if (e.item) {
                    if (!ck.itens) ck.itens = []
                    const membros = e.item.membros ?? e.item.Membros
                    const item = {
                        ...e.item,
                        membros: Array.isArray(membros) ? membros : []
                    }
                    const idx = ck.itens.findIndex((i) => Number(i.id) === Number(item.id))
                    if (idx >= 0) ck.itens.splice(idx, 1, item)
                    else ck.itens.push(item)
                }
            })
        },
        patchTarefaChecklists(tarefaId, { acao, checklist_id, checklists }) {
            this.arrayListas.forEach((lista) => {
                const t = (lista.tarefas || []).find((x) => Number(x.id) === Number(tarefaId))
                if (!t) return
                if (Array.isArray(checklists)) {
                    t.checklists = checklists
                } else if (acao === 'delete' && checklist_id) {
                    t.checklists = (t.checklists || []).filter((c) => Number(c.id) !== Number(checklist_id))
                }
            })
        },
        async confirmAction() {
            if (this.confirmFn) await this.confirmFn()
        },
        onLog(e) {
            if (!e?.log) return
            const log = normalizeLog(e.log)
            this.atividades.unshift(log)
            if (this.atividades.length > 20) this.atividades.length = 20

            if (log.tarefa_id && Number(this.tarefaAtiva?.id) === Number(log.tarefa_id)) {
                if (!this.tarefaAtiva.logs) this.tarefaAtiva.logs = []
                if (!this.tarefaAtiva.logs.some((l) => Number(l.id) === Number(log.id))) {
                    this.tarefaAtiva.logs.unshift(log)
                }
                this.$refs.taskModal?.prependLog?.(log)
            }
        },
        applyComentarioRealtime(e) {
            if (!e?.tarefa_id) return
            const comentario = e.comentario ? normalizeComentario(e.comentario) : null
            const comentarioId = e.comentario_id || comentario?.id

            this.arrayListas.forEach((lista) => {
                const t = (lista.tarefas || []).find((x) => Number(x.id) === Number(e.tarefa_id))
                if (!t) return
                if (!t.comentarios) t.comentarios = []
                if (e.evento === 'delete' || (!comentario && comentarioId)) {
                    t.comentarios = t.comentarios.filter((c) => Number(c.id) !== Number(comentarioId))
                } else if (comentario) {
                    const idx = t.comentarios.findIndex((c) => Number(c.id) === Number(comentario.id))
                    if (idx >= 0) t.comentarios.splice(idx, 1, comentario)
                    else t.comentarios.unshift(comentario)
                }
                t.comentarios_count = t.comentarios.length
                t.bloqueios_count = t.comentarios.filter((c) => c.tipo === 'bloqueio').length
            })

            if (Number(this.tarefaAtiva?.id) === Number(e.tarefa_id)) {
                this.$refs.taskModal?.applyComentario?.(e)
            }
        },
        mergeListas(listaPayload) {
            if (!this.quadroAtivo || !Array.isArray(listaPayload)) return
            const norm = normalizeListas(listaPayload)
            norm.forEach((lista) => {
                const existing = this.arrayListas.find((l) => l.id === lista.id)
                if (existing) {
                    existing.titulo = lista.titulo
                    existing.ordem = lista.ordem
                    if (lista.tarefas && lista.tarefas.length) {
                        existing.tarefas = lista.tarefas
                    }
                } else {
                    this.arrayListas.push(lista)
                }
            })
            this.arrayListas.sort((a, b) => (a.ordem || 0) - (b.ordem || 0))
        },
        mergeListasOrdem(listaPayload) {
            if (!Array.isArray(listaPayload)) return
            listaPayload.forEach((item) => {
                const l = this.arrayListas.find((x) => x.id === item.id)
                if (l) l.ordem = item.ordem
            })
            this.arrayListas.sort((a, b) => (a.ordem || 0) - (b.ordem || 0))
        },
        applyTarefasLista(e) {
            if (!e) return
            if (e.idDelete) {
                this.arrayListas.forEach((lista) => {
                    if (lista.tarefas) {
                        lista.tarefas = lista.tarefas.filter((t) => t.id !== e.idDelete)
                    }
                })
                if (Number(this.tarefaAtiva?.id) === Number(e.idDelete)) {
                    this.fecharTarefa()
                }
            }
            if (Array.isArray(e.tarefas)) {
                const listaId = e.lista_id || (e.tarefas[0] && e.tarefas[0].lista_id)
                const lista = this.arrayListas.find((l) => l.id === listaId)
                if (lista) {
                    lista.tarefas = e.tarefas.map(normalizeTarefa)
                }
            }
        },
        applyTarefaUpdate(e) {
            if (e?.tarefa) {
                const tarefa = normalizeTarefa(e.tarefa)
                this.arrayListas.forEach((lista) => {
                    const t = (lista.tarefas || []).find((x) => x.id === tarefa.id)
                    if (t) Object.assign(t, tarefa)
                })
                if (this.tarefaAtiva?.id === tarefa.id) Object.assign(this.tarefaAtiva, tarefa)
                return
            }
            if (e?.membros && e?.tarefa_id) {
                this.arrayListas.forEach((lista) => {
                    const t = (lista.tarefas || []).find((x) => x.id === e.tarefa_id)
                    if (t) t.membros = e.membros
                })
                if (this.tarefaAtiva?.id === e.tarefa_id) this.tarefaAtiva.membros = e.membros
                this.$refs.taskModal?.applyMembros?.(e.membros)
                return
            }
            if (e?.tarefa_id) {
                this.arrayListas.forEach((lista) => {
                    const t = (lista.tarefas || []).find((x) => x.id === e.tarefa_id)
                    if (t) {
                        if (e.datahora_entrega !== undefined) t.datahora_entrega = e.datahora_entrega
                        if (e.datahora_inicio !== undefined) t.datahora_inicio = e.datahora_inicio
                        if (e.concluido !== undefined) t.concluido = e.concluido
                        if (e.emAtraso !== undefined || e.em_atraso !== undefined) {
                            t.emAtraso = !!(e.emAtraso ?? e.em_atraso)
                        }
                    }
                })
                if (this.tarefaAtiva?.id === e.tarefa_id) Object.assign(this.tarefaAtiva, e)
            }
        },
        applyAnexos(e) {
            if (!e?.anexos || !e?.tarefa_id) return
            this.arrayListas.forEach((lista) => {
                const t = (lista.tarefas || []).find((x) => x.id === e.tarefa_id)
                if (t) t.anexos = e.anexos
            })
            if (this.tarefaAtiva?.id === e.tarefa_id) this.tarefaAtiva.anexos = e.anexos
        }
    }
}
</script>
